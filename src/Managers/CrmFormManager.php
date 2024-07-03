<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Exception;
use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Fahlgrendigital\StatamicFormManager\Support\FormConfig;
use Illuminate\Support\Facades\Http;
use Statamic\Forms\Submission;

class CrmFormManager extends BaseManager implements FormManager
{
    # CRM Defaults key/value pairs
    protected ?array $defaults = [];

    # CRM POST url
    public string $url = '';

    /**
     * @throws Exception
     */
    public static function init(string $key, array $config, ?string $subtype = null): FormManager
    {
        $form_config = new FormConfig($key, $config, $subtype);
        $url         = $form_config->value('::url');
        $maps        = $form_config->mergeValue('maps');
        $default     = $form_config->value('default');

        static::validateData(['url' => $url]);

        $instance           = new self;
        $instance->maps     = $maps;
        $instance->defaults = $default;
        $instance->url      = $url;

        if($form_config->localValue('::gate')){
            $instance->registerFormGate($form_config->localValue('::gate'));
        }

        return $instance;
    }

    # Prep submission data for CRM
    protected function prepData(Submission $submission): array
    {
        $data = $this->mappedData($submission->toArray());

        if (!empty($this->defaults)) {
            $data = array_merge($this->defaults, $data);
        }

        return $data;
    }

    public static function rules(): array
    {
        return [
            'url' => ['required', 'string']
        ];
    }

    protected function makeRequest(array $data): bool
    {
        return Http::asForm()->post($this->url, $data)->successful();
    }
}