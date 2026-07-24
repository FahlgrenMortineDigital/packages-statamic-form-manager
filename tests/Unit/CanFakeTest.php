<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Tests\Stubs\TestConnection;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;

class CanFakeTest extends TestCase
{
    public function test_is_faking_defaults_to_false()
    {
        $connection = new TestConnection();

        $this->assertFalse($connection->isFaking());
    }

    public function test_fake_it_flips_faking_and_is_fluent()
    {
        $connection = new TestConnection();

        $result = $connection->fakeIt();

        $this->assertTrue($connection->isFaking());
        $this->assertSame($connection, $result);
    }

    public function test_get_fake_response_with_no_mode_set_is_not_successful()
    {
        $connection = new TestConnection();

        $response = $connection->getFakeResponse();

        $this->assertFalse($response->success);
    }

    public function test_fake_success_makes_fake_response_successful_and_is_fluent()
    {
        $connection = new TestConnection();

        $result = $connection->fakeSuccess();

        $this->assertTrue($connection->getFakeResponse()->success);
        $this->assertSame($connection, $result);
    }

    public function test_fake_fail_makes_fake_response_unsuccessful_and_is_fluent()
    {
        $connection = new TestConnection();

        $result = $connection->fakeFail();

        $this->assertFalse($connection->getFakeResponse()->success);
        $this->assertSame($connection, $result);
    }

    public function test_last_fake_mode_call_wins()
    {
        $connection = new TestConnection();

        $connection->fakeSuccess();
        $connection->fakeFail();

        $this->assertFalse($connection->getFakeResponse()->success);

        $connection->fakeSuccess();

        $this->assertTrue($connection->getFakeResponse()->success);
    }

    public function test_get_fake_response_returns_distinct_instances()
    {
        $connection = new TestConnection();
        $connection->fakeSuccess();

        $first  = $connection->getFakeResponse();
        $second = $connection->getFakeResponse();

        $this->assertNotSame($first, $second);
    }
}
