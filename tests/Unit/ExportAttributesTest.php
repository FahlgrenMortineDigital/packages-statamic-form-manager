<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Data\Export;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class ExportAttributesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // getConnectionName() reads this config key to resolve the model's
        // connection. No query ever runs in these tests (no save()/find()), but
        // the accessor machinery still resolves the connection to read the date
        // format, so this must name a connection that actually exists.
        Config::set('statamic-formidable.exports.connection', 'sqlite');
    }

    public function test_is_pending_when_neither_exported_nor_failed()
    {
        $export = new Export(['exported_at' => null, 'failed_at' => null]);

        $this->assertTrue($export->is_pending);
        $this->assertFalse($export->is_completed);
        $this->assertFalse($export->is_failed);
    }

    public function test_is_completed_when_exported_and_not_failed()
    {
        $export = new Export(['exported_at' => now(), 'failed_at' => null]);

        $this->assertTrue($export->is_completed);
        $this->assertFalse($export->is_pending);
        $this->assertFalse($export->is_failed);
    }

    public function test_is_pending_false_when_only_exported_at_is_set()
    {
        $export = new Export(['exported_at' => null, 'failed_at' => now()]);

        $this->assertFalse($export->is_pending);
    }

    public function test_is_failed_when_failed_at_set()
    {
        $export = new Export(['exported_at' => null, 'failed_at' => now()]);

        $this->assertTrue($export->is_failed);
        $this->assertFalse($export->is_completed);
        $this->assertFalse($export->is_pending);
    }

    public function test_is_failed_wins_when_both_exported_and_failed_are_set()
    {
        $export = new Export(['exported_at' => now(), 'failed_at' => now()]);

        $this->assertTrue($export->is_failed);
        $this->assertFalse($export->is_completed);
        $this->assertFalse($export->is_pending);
    }

    public function test_exported_at_is_cast_to_a_carbon_instance()
    {
        $export = new Export();
        $export->setRawAttributes(['exported_at' => '2024-01-01 00:00:00', 'failed_at' => null]);

        $this->assertInstanceOf(Carbon::class, $export->exported_at);
        $this->assertTrue($export->is_completed);
    }
}
