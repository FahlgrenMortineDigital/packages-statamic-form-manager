<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Tests\Stubs\TestAction;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;

class BaseActionTest extends TestCase
{
    public function test_make_returns_instance_of_called_class()
    {
        $action = TestAction::make();

        $this->assertInstanceOf(TestAction::class, $action);
    }

    public function test_make_forwards_constructor_arguments()
    {
        $action = TestAction::make('x', 'y');

        $this->assertSame('x', $action->a);
        $this->assertSame('y', $action->b);
    }

    public function test_make_works_with_zero_arguments()
    {
        $action = TestAction::make();

        $this->assertNull($action->a);
        $this->assertNull($action->b);
    }
}
