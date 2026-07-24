<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Data\SubmissionWrapper;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;
use Statamic\Forms\Submission as FlatFileSubmission;

class SubmissionWrapperTest extends TestCase
{
    public function test_id_returns_null_when_wrapped_object_is_not_a_statamic_submission()
    {
        $plain = new class {
            public $unrelated = 'value';
        };

        $wrapper = new SubmissionWrapper($plain);

        $this->assertNull($wrapper->id());
    }

    public function test_id_delegates_to_flat_file_submission_id()
    {
        $submission = new FlatFileSubmission();
        $submission->id('abc-123');

        $wrapper = new SubmissionWrapper($submission);

        $this->assertSame('abc-123', $wrapper->id());
    }

    public function test_to_array_delegates_to_wrapped_object_without_injecting_id()
    {
        // Deliberately NOT a real Statamic\Forms\Submission here — its toArray()
        // needs a fully configured form/blueprint (Stache), which is out of scope
        // for a pure unit test. A plain fixture proves the delegation + "no id
        // injected for non-Eloquent submissions" behavior just as well.
        $fixture = new class {
            public function toArray(): array
            {
                return ['name' => 'Ada', 'email' => 'ada@example.com'];
            }
        };

        $wrapper = new SubmissionWrapper($fixture);

        $this->assertSame(['name' => 'Ada', 'email' => 'ada@example.com'], $wrapper->toArray());
    }

    public function test_call_forwards_method_and_arguments_to_wrapped_object()
    {
        $fixture = new class {
            public function add($a, $b)
            {
                return $a + $b;
            }
        };

        $wrapper = new SubmissionWrapper($fixture);

        $this->assertSame(3, $wrapper->add(1, 2));
    }

    public function test_call_throws_when_wrapped_object_lacks_the_method()
    {
        $fixture = new class {
        };

        $wrapper = new SubmissionWrapper($fixture);

        $this->expectException(\Error::class);

        $wrapper->methodThatDoesNotExist();
    }

    public function test_get_returns_wrapped_objects_public_property()
    {
        $fixture = new class {
            public string $form = 'contact-form';
        };

        $wrapper = new SubmissionWrapper($fixture);

        $this->assertSame('contact-form', $wrapper->form);
    }
}
