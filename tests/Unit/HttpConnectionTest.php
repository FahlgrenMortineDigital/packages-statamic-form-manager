<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Tests\Stubs\FailingResponseGate;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\PassingResponseGate;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\TestHttpConnection;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class HttpConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    private function connection(string $verb = 'get'): TestHttpConnection
    {
        $connection      = new TestHttpConnection();
        $connection->url = 'https://example.test/endpoint';

        return $connection
            ->setHandle('test-handle')
            ->callSetHttpVerb($verb);
    }

    public function test_get_request_sends_data_as_query_params()
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $response = $this->connection('get')->callMakeRequest(['foo' => 'bar']);

        $this->assertTrue($response->success);

        Http::assertSent(function (Request $request) {
            return $request->method() === 'GET' && $request['foo'] === 'bar';
        });
    }

    public function test_post_request_sends_json_body_by_default()
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->connection('post')->callMakeRequest(['foo' => 'bar']);

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST'
                && $request['foo'] === 'bar'
                && $request->hasHeader('Content-Type', 'application/json');
        });
    }

    public function test_post_as_form_sends_form_encoded_body()
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $connection = $this->connection('post');
        $connection->asForm = true;

        $connection->callMakeRequest(['foo' => 'bar']);

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST'
                && $request['foo'] === 'bar'
                && $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded');
        });
    }

    public function test_put_falls_through_to_get_request()
    {
        // SUSPECTED BUG: makeRequest() only branches on `$this->method === 'POST'`.
        // A PUT verb falls into the `else` branch and issues a GET request instead.
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->connection('put')->callMakeRequest(['foo' => 'bar']);

        Http::assertSent(function (Request $request) {
            return $request->method() === 'GET';
        });
    }

    public function test_custom_headers_are_sent_with_request()
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $connection = $this->connection('get');
        $connection->setHeaders(['X-Custom-Header' => 'yes']);

        $connection->callMakeRequest([]);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('X-Custom-Header', 'yes');
        });
    }

    public function test_headers_getter_defaults_to_empty_array_and_reflects_set_value()
    {
        $connection = new TestHttpConnection();

        $this->assertSame([], $connection->headers());

        $connection->setHeaders(['X-Test' => '1']);

        $this->assertSame(['X-Test' => '1'], $connection->headers());
    }

    public function test_pass_state_true_for_successful_response_with_no_gate()
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $response = $this->connection('get')->callMakeRequest([]);

        $this->assertTrue($response->success);
    }

    public function test_pass_state_false_for_failed_response_with_no_gate()
    {
        Http::fake(['*' => Http::response(['error' => true], 500)]);

        $response = $this->connection('get')->callMakeRequest([]);

        $this->assertFalse($response->success);
    }

    public function test_pass_state_gate_overrides_successful_response_to_fail()
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $connection = $this->connection('get');
        $connection->callRegisterResponseGate(FailingResponseGate::class);

        $response = $connection->callMakeRequest([]);

        $this->assertFalse($response->success);
    }

    public function test_pass_state_gate_overrides_failed_response_to_pass()
    {
        Http::fake(['*' => Http::response(['error' => true], 500)]);

        $connection = $this->connection('get');
        $connection->callRegisterResponseGate(PassingResponseGate::class);

        $response = $connection->callMakeRequest([]);

        $this->assertTrue($response->success);
    }

    public function test_guzzle_response_is_populated_on_connector_response()
    {
        Http::fake(['*' => Http::response(['ok' => true], 201)]);

        $response = $this->connection('get')->callMakeRequest([]);

        $this->assertSame(201, $response->guzzleResponse->status());
    }

    public function test_make_request_without_verb_ever_set_throws()
    {
        // CHARACTERIZATION: HasHttpVerbs::$method is a typed property with no
        // default; if setHttpVerb()/init() never ran, comparing $this->method
        // inside makeRequest() throws \Error rather than defaulting to GET.
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $connection = new TestHttpConnection();

        $this->expectException(\Error::class);

        $connection->callMakeRequest([]);
    }
}
