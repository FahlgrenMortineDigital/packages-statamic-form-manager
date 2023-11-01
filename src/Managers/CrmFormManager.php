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

class CrmFormManager extends BaseManager implements FormManager
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

        /** @var FormManager $instance */
        $instance = static::make($maps, $url, $default);

        if (Arr::has($config, '::gate') && Arr::get($config, '::gate')) {
            $instance->registerFormGate(Arr::get($config, '::gate'));
        }

        return $instance;
    }

    public function send(Submission $submission): bool
    {
        $prepped_data = $this->prepData($submission);

        if($this->debug) {
            Log::debug(json_encode($prepped_data));
        }

        if (!$this->shouldSend($submission->toArray())) {
            return false;
        }

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
        $data = [];

        collect($form_data)->each(function ($value, $key) use (&$data, $form_data) {
            if (!array_key_exists($key, $this->maps)) {
                return true;
            }

            $map_key = $this->maps[$key];

            if (is_array($map_key)) {
                $map_key     = $map_key[0];
                $transformer = $map_key[1];

                if (class_exists($transformer)) {
                    $value = (new $transformer)->handle($key, $value, $form_data);
                } else if (is_callable($transformer)) {
                    $value = $transformer($key, $value, $form_data);
                }
            }

            $data[$map_key] = $value;

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