<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Tests\Stubs\FailingResponseGate;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\PassingResponseGate;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\TestHttpConnection;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response;

class GatesResponsesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PassingResponseGate::$received     = null;
        PassingResponseGate::$lastInstance = null;
        FailingResponseGate::$received     = null;
        FailingResponseGate::$lastInstance = null;
    }

    public function test_has_registered_response_gate_defaults_to_false()
    {
        $connection = new TestHttpConnection();

        $this->assertFalse($connection->callHasRegisteredResponseGate());
    }

    public function test_register_response_gate_is_fluent_and_registers()
    {
        $connection = new TestHttpConnection();

        $result = $connection->callRegisterResponseGate(PassingResponseGate::class);

        $this->assertSame($connection, $result);
        $this->assertTrue($connection->callHasRegisteredResponseGate());
    }

    public function test_responses_passes_returns_gate_verdict()
    {
        $response = new Response(new Psr7Response(200));

        $passing = (new TestHttpConnection())->callRegisterResponseGate(PassingResponseGate::class);
        $failing = (new TestHttpConnection())->callRegisterResponseGate(FailingResponseGate::class);

        $this->assertTrue($passing->callResponsesPasses($response));
        $this->assertFalse($failing->callResponsesPasses($response));
        $this->assertSame($response, PassingResponseGate::$received);
    }

    public function test_responses_passes_resolves_gate_via_container()
    {
        $boundGate = new PassingResponseGate();
        $this->app->instance(PassingResponseGate::class, $boundGate);

        $connection = (new TestHttpConnection())->callRegisterResponseGate(PassingResponseGate::class);
        $connection->callResponsesPasses(new Response(new Psr7Response(200)));

        // Proves responsesPasses() resolved the gate via app() (container-bound
        // instance was used), not a fresh `new $gate`.
        $this->assertSame($boundGate, PassingResponseGate::$lastInstance);
    }
}
