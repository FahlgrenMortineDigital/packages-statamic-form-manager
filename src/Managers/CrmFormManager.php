<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Fahlgrendigital\StatamicFormManager\Exceptions\MissingManagerConfigParamException;
use Fahlgrendigital\StatamicFormManager\Managers\Traits\CanFake;
use Fahlgrendigital\StatamicFormManager\Managers\Traits\Subtypeable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Statamic\Forms\Submission;

class CrmFormManager implements FormManager
{
    use CanFake;
    use Subtypeable;

    # CRM => Statamic form field mappings
    protected array $maps = [];

    # CRM Defaults key/value pairs
    protected ?array $defaults = [];

    # CRM POST url
    public string $url = '';

    # Create a new FormManager instance fluently
    public static function make(array $maps, string $url = '', ?array $defaults = []): self
    {
        $manager           = new self;
        $manager->maps     = $maps;
        $manager->defaults = $defaults;
        $manager->url      = $url;

        return $manager;
    }

    public static function init(string $key, array $config, ?string $subtype = null): FormManager
    {
        $global_key    = static::buildConfigKey($key, $subtype);
        $global_config = config(sprintf("statamic-forms.defaults.%s", $global_key), []);
        $url           = Arr::get($config, '::url', Arr::get($global_config, '::url'));
        $maps          = array_merge(
            Arr::get($global_config, 'maps', []),
            Arr::get($config, 'maps', [])
        );
        $default       = array_merge(
            Arr::get($global_config, 'default', []),
            Arr::get($config, 'default', [])
        );

        if (empty($url)) {
            throw new MissingManagerConfigParamException("Missing required config param [::url]");
        }

        return static::make($maps, $url, $default);
    }

    public function send(Submission $submission): bool
    {
        $prepped_data = $this->prepData($submission);

        if ($this->isFaking()) {
            Log::debug('Data for CrmFormManager', $prepped_data);
            return $this->getFakeResponse();
        }

        $response = Http::asForm()->post($this->url, $prepped_data);

        return $response->successful();
    }

    # Map statamic form data to CRM fields
    protected function mappedData(array $form_data): array
    {
        $map  = array_flip($this->maps);
        $data = [];

        collect($form_data)->each(function ($value, $key) use ($map, &$data) {
            if (!array_key_exists($key, $map)) {
                return true;
            }

            $data[$map[$key]] = $value;

            return true;
        });

        return $data;
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
}