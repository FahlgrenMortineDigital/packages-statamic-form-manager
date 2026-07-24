<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Blueprints\ExportBlueprint;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;

class ExportBlueprintTest extends TestCase
{
    public function test_invoke_returns_expected_field_handles_and_types_in_order()
    {
        $blueprint = (new ExportBlueprint())();

        // setContents() normalizes the raw 'sections' key we passed in into
        // ['tabs']['main']['sections'][0]['fields'] internally (see
        // Blueprint::normalizeTabs()) — that's the shape contents() exposes.
        $fields = $blueprint->contents()['tabs']['main']['sections'][0]['fields'];

        $handles = array_column($fields, 'handle');
        $types   = array_column(array_column($fields, 'field'), 'type');

        $this->assertSame([
            'form_handle',
            'submission_id',
            'exported_count',
            'failed_count',
            'pending_count',
            'earliest_created_at',
            'completed',
        ], $handles);

        $this->assertSame(
            ['text', 'text', 'text', 'text', 'text', 'date', 'toggle'],
            $types
        );
    }
}
