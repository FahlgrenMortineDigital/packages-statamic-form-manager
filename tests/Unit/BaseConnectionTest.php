<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Connector\ConnectorResponse;
use Fahlgrendigital\StatamicFormManager\Exceptions\MissingFormFieldTransformerException;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\ArraySubmission;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\BadTransformer;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\BlockingFormGate;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\CallableGate;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\CallableTransformer;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\ComputedKeyCallable;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\GoodTransformer;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\PassingFormGate;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\TestConnection;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;

class BaseConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TestConnection::$validationRules = [];
        PassingFormGate::$received       = null;
        BlockingFormGate::$received      = null;
        ComputedKeyCallable::$received   = null;
    }

    // -------------------------
    //      TRIVIAL FLUENT SETTERS
    // -------------------------

    public function test_debug_is_fluent()
    {
        $connection = new TestConnection();

        $this->assertSame($connection, $connection->debug(true));
    }

    public function test_register_form_gate_is_fluent()
    {
        $connection = new TestConnection();

        $this->assertSame($connection, $connection->registerFormGate('anything'));
    }

    public function test_get_handle_returns_set_handle()
    {
        $connection = (new TestConnection())->setHandle('my-handle');

        $this->assertSame('my-handle', $connection->getHandle());
    }

    public function test_get_handle_on_fresh_instance_throws()
    {
        // CHARACTERIZATION: getHandle() is declared `: string` but $handle defaults to
        // null, so calling it before setHandle()/registerFormGate() throws a TypeError.
        $connection = new TestConnection();

        $this->expectException(\TypeError::class);

        $connection->getHandle();
    }

    // -------------------------
    //      shouldSend()
    // -------------------------

    public function test_should_send_is_true_when_no_gate_registered()
    {
        $connection = new TestConnection();

        $this->assertTrue($connection->callShouldSend(['a' => 1]));
    }

    public function test_should_send_true_via_passing_form_gate_class()
    {
        $connection = (new TestConnection())->setGate(PassingFormGate::class);

        $form_data = ['email' => 'a@example.com'];

        $this->assertTrue($connection->callShouldSend($form_data));
        $this->assertSame($form_data, PassingFormGate::$received);
    }

    public function test_should_send_false_via_blocking_form_gate_class()
    {
        $connection = (new TestConnection())->setGate(BlockingFormGate::class);

        $form_data = ['email' => 'a@example.com'];

        $this->assertFalse($connection->callShouldSend($form_data));
        $this->assertSame($form_data, BlockingFormGate::$received);
    }

    public function test_should_send_via_string_callable_gate()
    {
        $allow = (new TestConnection())->setGate(CallableGate::class . '::allow');
        $deny  = (new TestConnection())->setGate(CallableGate::class . '::deny');

        $this->assertTrue($allow->callShouldSend(['x' => 1]));
        $this->assertFalse($deny->callShouldSend(['x' => 1]));
    }

    public function test_should_send_true_when_gate_is_neither_form_gate_nor_callable()
    {
        // BadTransformer is a real, existing class but implements neither FormGate
        // nor a callable signature, so shouldSend() falls through to true.
        $connection = (new TestConnection())->setGate(BadTransformer::class);

        $this->assertTrue($connection->callShouldSend([]));
    }

    public function test_registering_a_closure_gate_throws()
    {
        // CHARACTERIZATION: $gate is declared `protected string $gate`, so assigning
        // a Closure (rather than a class-string or "Class::method" string) throws a
        // TypeError immediately on registration — a closure gate can never work.
        $connection = new TestConnection();

        $this->expectException(\TypeError::class);

        $connection->setGate(function () {
            return true;
        });
    }

    // -------------------------
    //      mappedData()
    // -------------------------

    public function test_mapped_data_renames_mapped_keys_and_drops_unmapped()
    {
        $connection = (new TestConnection())->setMaps(['crm_1' => 'email']);

        $result = $connection->mappedData(['crm_1' => 'a@example.com', 'other' => 'dropped']);

        $this->assertSame(['email' => 'a@example.com'], $result);
    }

    public function test_mapped_data_skips_map_entries_missing_from_form_data()
    {
        $connection = (new TestConnection())->setMaps(['crm_1' => 'email', 'crm_2' => 'name']);

        $result = $connection->mappedData(['crm_1' => 'a@example.com']);

        $this->assertSame(['email' => 'a@example.com'], $result);
    }

    public function test_mapped_data_array_config_with_valid_transformer_class()
    {
        $connection = (new TestConnection())->setMaps([
            'crm_1' => ['mapped_email', GoodTransformer::class],
        ]);

        $result = $connection->mappedData(['crm_1' => 'a@example.com']);

        $this->assertSame('test', $result['mapped_email']);
    }

    public function test_mapped_data_array_config_with_string_callable_transformer_receives_all_three_args()
    {
        // Only a *string* callable ("Class::method") reaches the is_callable() branch —
        // see the Closure characterization test below for why.
        $connection = (new TestConnection())->setMaps([
            'crm_1' => ['mapped_email', CallableTransformer::class . '::transform'],
        ]);

        $form_data = ['crm_1' => 'a@example.com'];
        $result    = $connection->mappedData($form_data);

        $this->assertSame('crm_1|a@example.com|1', $result['mapped_email']);
    }

    public function test_mapped_data_array_config_with_closure_transformer_throws_type_error()
    {
        // CHARACTERIZATION: the maps branch calls class_exists($transformer) first,
        // just like the computed branch. A real Closure object isn't a string, so
        // class_exists() throws a TypeError before is_callable($transformer) — which
        // *does* correctly check the value here, unlike the computed branch's bug —
        // is ever reached. Closures can never be used as transformers; only
        // class-strings and "Class::method" strings work.
        $connection = (new TestConnection())->setMaps([
            'crm_1' => ['mapped_email', function ($key, $value, $form_data) {
                return $value;
            }],
        ]);

        $this->expectException(\TypeError::class);

        $connection->mappedData(['crm_1' => 'a@example.com']);
    }

    public function test_mapped_data_array_config_with_invalid_transformer_throws()
    {
        $connection = (new TestConnection())->setMaps([
            'crm_1' => ['mapped_email', BadTransformer::class],
        ]);

        $this->expectException(MissingFormFieldTransformerException::class);

        $connection->mappedData(['crm_1' => 'a@example.com']);
    }

    public function test_mapped_data_computed_with_valid_transformer_class()
    {
        $connection = (new TestConnection())->setComputed(['computed_1' => GoodTransformer::class]);

        $result = $connection->mappedData([]);

        $this->assertSame('test', $result['computed_1']);
    }

    public function test_mapped_data_computed_with_invalid_class_throws()
    {
        $connection = (new TestConnection())->setComputed(['computed_1' => BadTransformer::class]);

        $this->expectException(MissingFormFieldTransformerException::class);

        $connection->mappedData([]);
    }

    public function test_mapped_data_computed_closure_value_throws_type_error()
    {
        // CHARACTERIZATION: the computed branch calls class_exists($value) first.
        // Passing a Closure as the value trips a TypeError (class_exists() requires
        // a string) before the code ever reaches the buggy is_callable() check below.
        $connection = (new TestConnection())->setComputed([
            'computed_1' => function () {
                return 'x';
            },
        ]);

        $this->expectException(\TypeError::class);

        $connection->mappedData([]);
    }

    public function test_mapped_data_computed_invokes_via_key_not_configured_value()
    {
        // SUSPECTED BUG: BaseConnection::mappedData()'s computed loop checks
        // is_callable($key) instead of is_callable($value). So a computed transformer
        // only runs when the FIELD NAME (array key) itself happens to be a callable
        // string — the actually-configured value is completely ignored.
        $callableKey = ComputedKeyCallable::class . '::handle';

        $connection = (new TestConnection())->setComputed([
            $callableKey => 'this-configured-value-is-ignored',
        ]);

        $result = $connection->mappedData([]);

        $this->assertSame('invoked-via-key', $result[$callableKey]);
        $this->assertSame($callableKey, ComputedKeyCallable::$received['key']);
        $this->assertNull(ComputedKeyCallable::$received['value']);
    }

    public function test_mapped_data_computed_overwrites_mapped_value_with_same_output_key()
    {
        // The computed loop runs strictly after the maps loop and assigns directly
        // into $data[$key] with no collision check — so a computed field sharing a
        // mapped field's output key silently wins.
        $connection = (new TestConnection())
            ->setMaps(['crm_1' => 'email'])
            ->setComputed(['email' => GoodTransformer::class]);

        $result = $connection->mappedData(['crm_1' => 'a@example.com']);

        $this->assertSame('test', $result['email']);
    }

    public function test_mapped_data_merges_defaults_and_is_overridden_by_mapped_values()
    {
        $connection = (new TestConnection())
            ->setMaps(['crm_1' => 'email'])
            ->setDefaults(['email' => 'default@example.com', 'source' => 'website']);

        $result = $connection->mappedData(['crm_1' => 'a@example.com']);

        $this->assertSame([
            'email'  => 'a@example.com',
            'source' => 'website',
        ], $result);
    }

    public function test_mapped_data_with_empty_defaults_is_untouched()
    {
        $connection = (new TestConnection())->setMaps(['crm_1' => 'email']);

        $result = $connection->mappedData(['crm_1' => 'a@example.com']);

        $this->assertSame(['email' => 'a@example.com'], $result);
    }

    public function test_mapped_data_keeps_null_transformer_result()
    {
        // BadTransformer::handle() returns null; when it IS registered as a valid
        // FormFieldTransformer, mappedData still sets the key via array_key_exists,
        // not isset — so a null result is preserved, not dropped.
        $connection = (new TestConnection())->setMaps([
            'crm_1' => ['mapped_email', new class implements \Fahlgrendigital\StatamicFormManager\Contracts\FormFieldTransformer {
                public function handle(string $key, mixed $value, array $form_data): mixed
                {
                    return null;
                }
            }::class],
        ]);

        $result = $connection->mappedData(['crm_1' => 'a@example.com']);

        $this->assertArrayHasKey('mapped_email', $result);
        $this->assertNull($result['mapped_email']);
    }

    // -------------------------
    //      send()
    // -------------------------

    public function test_send_happy_path_calls_make_request_once_with_prepped_data()
    {
        $connection               = new TestConnection();
        $connection->preppedData  = ['prepped' => true];
        $connection->requestResponse = (new ConnectorResponse())->setPassState(true);

        $response = $connection->send(new ArraySubmission(['raw' => true]));

        $this->assertSame($connection->requestResponse, $response);
        $this->assertSame(1, $connection->makeRequestCalls);
        $this->assertSame(['prepped' => true], $connection->lastRequestData);
    }

    public function test_send_returns_failed_response_when_gate_blocks_and_never_calls_make_request()
    {
        $connection = (new TestConnection())->setGate(BlockingFormGate::class);

        $response = $connection->send(new ArraySubmission(['email' => 'a@example.com']));

        $this->assertFalse($response->success);
        $this->assertSame(0, $connection->makeRequestCalls);
    }

    public function test_send_returns_fake_response_when_faking_and_never_calls_make_request()
    {
        $connection = (new TestConnection())->fakeIt()->fakeSuccess();

        $response = $connection->send(new ArraySubmission([]));

        $this->assertTrue($response->success);
        $this->assertSame(0, $connection->makeRequestCalls);
    }

    public function test_send_gate_check_wins_over_faking()
    {
        $connection = (new TestConnection())->setGate(BlockingFormGate::class)->fakeIt()->fakeSuccess();

        $response = $connection->send(new ArraySubmission([]));

        $this->assertFalse($response->success);
        $this->assertSame(0, $connection->makeRequestCalls);
    }

    // -------------------------
    //      validateData()
    // -------------------------

    public function test_validate_data_passes_with_no_rules()
    {
        TestConnection::$validationRules = [];

        $this->assertTrue(TestConnection::callValidateData(['anything' => 'goes']));
    }

    public function test_validate_data_passes_when_rules_satisfied()
    {
        TestConnection::$validationRules = ['url' => ['required', 'string']];

        $this->assertTrue(TestConnection::callValidateData(['url' => 'https://example.com']));
    }

    public function test_validate_data_throws_with_field_errors_when_rules_fail()
    {
        TestConnection::$validationRules = ['url' => ['required', 'string']];

        try {
            TestConnection::callValidateData(['url' => '']);
            $this->fail('Expected exception was not thrown.');
        } catch (\Exception $e) {
            $this->assertStringStartsWith('statamic-formidable: Validation failed for:', $e->getMessage());
            $this->assertStringContainsString('url', $e->getMessage());
        }
    }
}
