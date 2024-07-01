<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Fahlgrendigital\StatamicFormManager\Support\FormConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Statamic\Forms\Submission;

class RestApiManager extends BaseManager implements FormManager
{
    # CRM Defaults key/value pairs
    protected ?array $defaults = [];

    # CRM POST url
    public string $url = '';

    public string $api_key = '';

    protected function prepData(Submission $submission): array
    {
        $data = $this->mappedData($submission->toArray());

        if (!empty($this->defaults)) {
            $data = array_merge($this->defaults, $data);
        }

        return $data;
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