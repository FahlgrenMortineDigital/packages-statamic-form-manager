<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Fahlgrendigital\StatamicFormManager\Managers\Traits\CanFake;
use Fahlgrendigital\StatamicFormManager\Managers\Traits\Subtypeable;
use Illuminate\Support\Facades\Log;
use Statamic\Forms\Submission;

class RestApiManager extends BaseManager implements FormManager
{
    use CanFake;
    use Subtypeable;

    protected function prepData(Submission $submission): array
    {
        return $submission->toArray();
    }

    public function send(Submission $submission): bool
    {
        $prepped_data = $this->prepData($submission);

        if($this->debug) {
            Log::debug(json_encode($prepped_data));
        }

        if (!$this->shouldSend($prepped_data)) {
            return false;
        }
    }

    public static function init(string $key, array $config, ?string $subtype = null): FormManager
    {
        // TODO: Implement init() method.
    }

    public static function rules(): array
    {
        // TODO: Implement rules() method.
    }
}