<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Data\SubmissionWrapper;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\EloquentSubmissionStub;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;

class SubmissionWrapperEloquentTest extends TestCase
{
    private static bool $aliasHandled = false;
    private static bool $skip = false;

    protected function setUp(): void
    {
        parent::setUp();

        // The real class-existence check must run exactly once, before we ever
        // create our own alias — otherwise a later setUp() call would see the
        // alias we created and wrongly conclude the real driver is installed.
        if (!self::$aliasHandled) {
            self::$skip = class_exists('\Statamic\Eloquent\Forms\Submission');

            if (!self::$skip) {
                class_alias(EloquentSubmissionStub::class, 'Statamic\Eloquent\Forms\Submission');
            }

            self::$aliasHandled = true;
        }

        if (self::$skip) {
            $this->markTestSkipped('statamic/eloquent-driver is installed; the stub alias is not applicable.');
        }
    }

    public function test_id_returns_model_id()
    {
        $submission = new EloquentSubmissionStub([], 5);

        $wrapper = new SubmissionWrapper($submission);

        // CHARACTERIZATION: this file has no declare(strict_types=1), so returning
        // an int model id through a `: ?string` return type silently coerces it.
        $this->assertSame('5', $wrapper->id());
    }

    public function test_to_array_adds_and_overwrites_id_from_model()
    {
        $submission = new EloquentSubmissionStub(['id' => 'stale', 'name' => 'Ada'], 7);

        $wrapper = new SubmissionWrapper($submission);

        // Unlike id() (which coerces via its `: ?string` return type), toArray()
        // assigns $data['id'] directly with no type coercion, so the int stays an int.
        $this->assertSame(['id' => 7, 'name' => 'Ada'], $wrapper->toArray());
    }
}
