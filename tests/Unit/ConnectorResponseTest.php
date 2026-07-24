<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Connector\ConnectorResponse;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;

class ConnectorResponseTest extends TestCase
{
    public function test_set_pass_state_sets_success_and_is_fluent()
    {
        $response = new ConnectorResponse();

        $result = $response->setPassState(true);

        $this->assertTrue($response->success);
        $this->assertSame($response, $result);

        $response->setPassState(false);

        $this->assertFalse($response->success);
    }

    public function test_accessing_unset_message_throws()
    {
        // CHARACTERIZATION: $message is a typed property with no default value, so reading it
        // before it's assigned throws \Error rather than returning null.
        $response = new ConnectorResponse();

        $this->expectException(\Error::class);

        $response->message;
    }

    public function test_accessing_unset_guzzle_response_throws()
    {
        // CHARACTERIZATION: same as above for $guzzleResponse.
        $response = new ConnectorResponse();

        $this->expectException(\Error::class);

        $response->guzzleResponse;
    }
}
