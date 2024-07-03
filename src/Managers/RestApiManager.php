<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Exception;
use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Fahlgrendigital\StatamicFormManager\Support\FormConfig;
use Illuminate\Support\Facades\Http;
use Statamic\Forms\Submission;

class RestApiManager extends BaseManager implements FormManager
{
    # CRM Defaults key/value pairs
    protected ?array $defaults = [];

    # CRM POST url
    public string $url = '';

    public ?array $headers = [];

    protected function prepData(Submission $submission): array
    {
        $data = $this->mappedData($submission->toArray());

        if (!empty($this->defaults)) {
            $data = array_merge($this->defaults, $data);
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public static function init(string $key, array $config, ?string $subtype = null): FormManager
    {
        $form_config = new FormConfig($key, $config, $subtype);
        $url         = $form_config->value('::url');
        $maps        = $form_config->mergeValue('maps');
        $default     = $form_config->value('default');
        $headers     = $form_config->value('::headers');

        static::validateData(['url' => $url]);

        $instance           = new self;
        $instance->maps     = $maps;
        $instance->defaults = $default;
        $instance->url      = $url;
        $instance->headers  = $headers;

        if ($form_config->localValue('::gate')) {
            $instance->registerFormGate($form_config->localValue('::gate'));
        }

        return $instance;
    }

    public static function rules(): array
    {
        return [
            'url'     => ['required', 'string']
        ];
    }

    protected function makeRequest(array $data): bool
    {
        return Http::withHeaders($this->headers)->asJson()->post($this->url, $data)->successful();
    }
}