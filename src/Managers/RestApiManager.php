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


    # CRM POST url
    public string $url = '';

    public ?array $headers = [];

    /**
     * @throws Exception
     */
    protected function prepData(Submission $submission): array
    {
        return $this->mappedData($submission->toArray());
    }

    /**
     * @throws Exception
     */
    public static function init(string $key, array $config, ?string $subtype = null): FormManager
    {
        $form_config = new FormConfig($key, $config, $subtype);
        $url         = $form_config->value('::url');
        $maps        = $form_config->mergeValue('maps');
        $computed    = $form_config->mergeValue('computed');
        $default     = $form_config->mergeValue('default');
        $headers     = $form_config->value('::headers');

        static::validateData(['url' => $url]);

        $instance           = new self;
        $instance->maps     = $maps;
        $instance->defaults = $default;
        $instance->url      = $url;
        $instance->headers  = $headers;
        $instance->computed = $computed;

        if ($form_config->localValue('::gate')) {
            $instance->registerFormGate($form_config->localValue('::gate'));
        }

        return $instance;
    }

    public static function rules(): array
    {
        return [
            'url' => ['required', 'string']
        ];
    }

    protected function makeRequest(array $data): bool
    {
        return Http::withHeaders($this->headers)->asJson()->post($this->url, $data)->successful();
    }
}