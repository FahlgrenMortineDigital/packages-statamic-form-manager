<?php

namespace Fahlgrendigital\StatamicFormManager\Connector;

use Exception;
use Fahlgrendigital\StatamicFormManager\Connector\Traits\GatesResponses;
use Fahlgrendigital\StatamicFormManager\Connector\Traits\HasHeaders;
use Fahlgrendigital\StatamicFormManager\Connector\Traits\HasHttpVerbs;
use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\Contracts\HttpConnector;
use Fahlgrendigital\StatamicFormManager\Contracts\SubmissionInterface;
use Fahlgrendigital\StatamicFormManager\Data\Export;
use Fahlgrendigital\StatamicFormManager\Support\FormConfig;
use Illuminate\Support\Facades\Http;
use Statamic\Forms\Submission;

class HttpConnection extends BaseConnection implements ConnectorContract, HttpConnector
{
    use HasHeaders;
    use HasHttpVerbs;
    use GatesResponses;

    public string $url = '';
    public static string $default_method = 'GET';

    public bool $asForm = false;

    /**
     * @throws Exception
     */
    public static function init(string $form_handle, string $key, ?string $subtype = null): ConnectorContract
    {
        $form_config   = new FormConfig($form_handle, $key, $subtype);
        $url           = $form_config->value('::url');
        $headers       = $form_config->value('::headers');
        $response_gate = $form_config->value('::response-gate');
        $maps          = $form_config->mergeValue('maps');
        $computed      = $form_config->mergeValue('computed');
        $default       = $form_config->mergeValue('default');
        $asForm        = $form_config->value('::as-form', false);

        static::validateData(['url' => $url]);

        $instance           = new self;
        $instance->maps     = $maps;
        $instance->defaults = $default;
        $instance->url      = $url;
        $instance->headers  = $headers;
        $instance->computed = $computed;
        $instance->handle   = $form_config->key();
        $instance->asForm   = $asForm;
        $instance->setHttpVerb($form_config->value('::method', static::$default_method));

        if ($response_gate) {
            $instance->registerResponseGate($response_gate);
        }

        if ($form_config->localValue('::gate')) {
            $instance->registerFormGate($form_config->localValue('::gate'));
        }

        return $instance;
    }

    /**
     * @throws Exception
     */
    protected function prepData(SubmissionInterface $submission): array
    {
        return $this->mappedData($submission->toArray());
    }

    public static function rules(): array
    {
        return [
            'url' => ['required', 'string']
        ];
    }

    protected function makeRequest(array $data): ConnectorResponse
    {
        $res = Http::withHeaders($this->headers ?? []);

        if ($this->method === 'POST') {
            if($this->asForm) {
                $res->asForm()->post($this->url, $data);
            } else {
                $res->post($this->url, $data);
            }
        } else {
            $res = $res->asJson()->get($this->url, $data);
        }

        $connectorResponse                 = (new ConnectorResponse());
        $connectorResponse->guzzleResponse = $res;

        $connectorResponse->setPassState(
            $this->hasRegisteredResponseGate()
                ? $this->responsesPasses($res)
                : $res->successful()
        );

        return $connectorResponse;
    }

    /**
     * @throws Exception
     */
    public function logPayload(SubmissionInterface $submission): bool
    {
        $data   = $this->prepData($submission);
        $export = Export::forSubmission($submission)->forConnection($this)->first();

        if (!$export) {
            return false;
        }

        $export->update(['submission_payload' => $data]);

        return true;
    }
}