<?php

namespace Fahlgrendigital\StatamicFormManager\Connector;

use Exception;
use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\Contracts\HttpConnector;
use Fahlgrendigital\StatamicFormManager\Support\FormConfig;
use Illuminate\Support\Facades\Http;
use Statamic\Forms\Submission;

class HttpConnection extends BaseConnection implements ConnectorContract, HttpConnector
{
    public string $url = '';
    public ?array $headers = [];

    /**
     * @throws Exception
     */
    public static function init(string $key, array $config, ?string $subtype = null): ConnectorContract
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
        $instance->handle   = $form_config->key();

        if ($form_config->localValue('::gate')) {
            $instance->registerFormGate($form_config->localValue('::gate'));
        }

        return $instance;
    }

    /**
     * @throws Exception
     */
    protected function prepData(Submission $submission): array
    {
        return $this->mappedData($submission->toArray());
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