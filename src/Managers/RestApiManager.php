<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Fahlgrendigital\StatamicFormManager\Managers\Traits\CanFake;
use Fahlgrendigital\StatamicFormManager\Support\FormConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Statamic\Forms\Submission;

class RestApiManager extends BaseManager implements FormManager
{
    use CanFake;

    # CRM Defaults key/value pairs
    protected ?array $defaults = [];

    # CRM POST url
    public string $url = '';

    public string $api_key = '';

    protected function prepData(Submission $submission): array
    {
        return $submission->toArray();
    }

    public function send(Submission $submission): bool
    {
        $prepped_data = $this->prepData($submission);

        if ($this->debug) {
            Log::debug(json_encode($prepped_data));
        }

        if (!$this->shouldSend($prepped_data)) {
            return false;
        }
    }

    public static function init(string $key, array $config, ?string $subtype = null): FormManager
    {
        $form_config = new FormConfig($key, $config, $subtype);
        $url         = $form_config->value('::url');
        $maps        = $form_config->mergeValue('maps');
        $default     = $form_config->value('default');
        $api_key     = $form_config->value('::api_key');

        Validator::make([
            'url'     => $url,
            'api_key' => $api_key
        ], static::rules())->validate();

        $instance           = new self;
        $instance->maps     = $maps;
        $instance->defaults = $default;
        $instance->url      = $url;

        if ($form_config->localValue('::gate')) {
            $instance->registerFormGate($form_config->localValue('::gate'));
        }
    }

    public static function rules(): array
    {
        return [
            'url'     => ['required', 'string'],
            'api_key' => ['required', 'string']
        ];
    }

    protected function makeRequest(array $data): bool
    {
        return Http::withHeaders(['X-API-Key' => $this->api_key])->asJson()->get($this->url, $data)->successful();
    }
}