<?php

namespace Fahlgrendigital\StatamicFormManager\Connector;

use Exception;
use Fahlgrendigital\StatamicFormManager\Connector\Traits\HasHeaders;
use Fahlgrendigital\StatamicFormManager\Connector\Traits\HasHttpVerbs;
use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\Contracts\HttpConnector;
use Fahlgrendigital\StatamicFormManager\Data\Export;
use Fahlgrendigital\StatamicFormManager\Support\FormConfig;
use Illuminate\Support\Facades\Http;
use Statamic\Forms\Submission;

class HttpConnection extends BaseConnection implements ConnectorContract, HttpConnector
{
    use HasHeaders;
    use HasHttpVerbs;

    public string $url = '';
    public static string $default_method = 'GET';

    /**
     * @throws Exception
     */
    public static function init(string $form_handle, string $key, ?string $subtype = null): ConnectorContract
    {
        $form_config = new FormConfig($form_handle, $key, $subtype);
        $url         = $form_config->value('::url');
        $headers     = $form_config->value('::headers');
        $maps        = $form_config->mergeValue('maps');
        $computed    = $form_config->mergeValue('computed');
        $default     = $form_config->mergeValue('default');

        static::validateData(['url' => $url]);

        $instance           = new self;
        $instance->maps     = $maps;
        $instance->defaults = $default;
        $instance->url      = $url;
        $instance->headers  = $headers;
        $instance->computed = $computed;
        $instance->handle   = $form_config->key();
        $instance->setHttpVerb($form_config->value('::method', static::$default_method));

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
        if ($this->method === 'POST') {
            return Http::withHeaders($this->headers)->post($this->url, $data)->successful();
        }

        return Http::withHeaders($this->headers ?? [])->asJson()->get($this->url, $data)->successful();
    }

    /**
     * @param Submission $submission
     * @return bool
     * @throws Exception
     */
    public function logPayload(Submission $submission): bool
    {
        $data   = $this->prepData($submission);
        $export = Export::forSubmission($submission)->where('destination', $this->getHandle())->first();

        if(!$export) {
            return false;
        }

        $export->update(['submission_payload' => $data]);

        return true;
    }
}