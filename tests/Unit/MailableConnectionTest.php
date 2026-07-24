<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Tests\Stubs\ArraySubmission;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\QueueableMailableStub;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\TestMailableConnection;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailableConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    private function connection(): TestMailableConnection
    {
        $connection = new TestMailableConnection();
        $connection->setMailable(QueueableMailableStub::class);

        return $connection;
    }

    public function test_set_and_get_mailable_round_trip()
    {
        $connection = new TestMailableConnection();

        $connection->setMailable(QueueableMailableStub::class);

        $this->assertSame(QueueableMailableStub::class, $connection->getMailable());
    }

    public function test_set_and_get_recipients_round_trip()
    {
        $connection = new TestMailableConnection();

        $connection->setRecipients(['a@example.com', 'b@example.com']);

        $this->assertSame(['a@example.com', 'b@example.com'], $connection->getRecipients());
    }

    public function test_make_request_queues_one_mailable_per_recipient()
    {
        $connection = $this->connection();
        $connection->setRecipients(['a@example.com']);

        $response = $connection->callMakeRequest(['name' => 'Ada']);

        $this->assertTrue($response->success);
        Mail::assertQueuedCount(1);
        Mail::assertQueued(
            QueueableMailableStub::class,
            fn ($mailable) => $mailable->hasTo('a@example.com')
        );
    }

    public function test_make_request_queues_one_mailable_per_recipient_with_multiple_recipients()
    {
        $connection = $this->connection();
        $connection->setRecipients(['a@example.com', 'b@example.com', 'c@example.com']);

        $connection->callMakeRequest(['name' => 'Ada']);

        Mail::assertQueuedCount(3);
        Mail::assertQueued(QueueableMailableStub::class, fn ($mailable) => $mailable->hasTo('a@example.com'));
        Mail::assertQueued(QueueableMailableStub::class, fn ($mailable) => $mailable->hasTo('b@example.com'));
        Mail::assertQueued(QueueableMailableStub::class, fn ($mailable) => $mailable->hasTo('c@example.com'));
    }

    public function test_make_request_with_no_recipients_queues_nothing_but_still_succeeds()
    {
        $connection = $this->connection();
        $connection->setRecipients([]);

        $response = $connection->callMakeRequest(['name' => 'Ada']);

        $this->assertTrue($response->success);
        Mail::assertNothingQueued();
    }

    public function test_mailable_receives_submitted_data_as_a_collection()
    {
        $connection = $this->connection();
        $connection->setRecipients(['a@example.com']);

        $data = ['name' => 'Ada', 'email' => 'a@example.com'];

        $connection->callMakeRequest($data);

        Mail::assertQueued(QueueableMailableStub::class, function ($mailable) use ($data) {
            return $mailable->data instanceof \Illuminate\Support\Collection
                && $mailable->data->all() === $data;
        });
    }

    public function test_queue_connection_reads_wrong_config_namespace()
    {
        // SUSPECTED BUG: makeRequest() calls config('statamic-form-manager.queue.queue')
        // and config('statamic-form-manager.queue.connection'), but this package's real
        // config namespace is 'statamic-formidable' everywhere else. Setting the
        // *actual* published config key has no effect on the queued mailable.
        Config::set('statamic-formidable.queue.queue', 'real-queue');
        Config::set('statamic-formidable.queue.connection', 'real-connection');

        $connection = $this->connection();
        $connection->setRecipients(['a@example.com']);

        $connection->callMakeRequest(['name' => 'Ada']);

        Mail::assertQueued(QueueableMailableStub::class, function ($mailable) {
            return $mailable->queue === null && $mailable->connection === null;
        });
    }

    public function test_make_request_without_recipients_ever_set_throws()
    {
        // CHARACTERIZATION: `protected array $recipients` has no default value, so
        // iterating it in makeRequest() before setRecipients() has ever run throws
        // \Error — consistent with the other uninitialized-typed-property behaviors
        // characterized elsewhere in this suite (BaseConnection::$handle,
        // ConnectorResponse::$message, HasHttpVerbs::$method).
        $connection = new TestMailableConnection();
        $connection->setMailable(QueueableMailableStub::class);

        $this->expectException(\Error::class);

        $connection->callMakeRequest(['name' => 'Ada']);
    }

    public function test_log_payload_always_returns_false()
    {
        $connection = new TestMailableConnection();

        $this->assertFalse($connection->logPayload(new ArraySubmission([])));
    }

    public function test_prep_data_returns_raw_submission_array_unmapped()
    {
        $connection = new TestMailableConnection();

        $data = ['a' => 1, 'b' => 2];

        $this->assertSame($data, $connection->callPrepData(new ArraySubmission($data)));
    }
}
