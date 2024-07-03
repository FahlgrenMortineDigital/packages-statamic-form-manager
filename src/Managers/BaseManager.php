<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Fahlgrendigital\StatamicFormManager\Contracts\FormFieldTransformer;
use Fahlgrendigital\StatamicFormManager\Contracts\FormGate;
use Fahlgrendigital\StatamicFormManager\Exceptions\MissingFormFieldTransformerException;
use Fahlgrendigital\StatamicFormManager\Managers\Traits\CanFake;
use Fahlgrendigital\StatamicFormManager\StatamicFormManagerProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Statamic\Forms\Submission;

abstract class BaseManager
{
    use CanFake;

    protected string $gate;
    protected bool $debug = false;

    # local => remote
    protected array $maps = [];

    protected array $computed = [];

    protected array $defaults = [];

    abstract protected function makeRequest(array $data): bool;

    abstract protected function prepData(Submission $submission): array;

    abstract public static function rules(): array;

    public function debug(bool $mode): self
    {
        $this->debug = $mode;

        return $this;
    }

    public function registerFormGate($gate): self
    {
        $this->gate = $gate;

        return $this;
    }

    public function send(Submission $submission): bool
    {
        $prepped_data = $this->prepData($submission);

        if ($this->debug) {
            Log::debug(sprintf('> %s: %s', StatamicFormManagerProvider::PACKAGE_NAME, json_encode($prepped_data)));
        }

        if (!$this->shouldSend($submission->toArray())) {
            if ($this->debug) {
                Log::debug(sprintf('> %s: CRM gate failed', StatamicFormManagerProvider::PACKAGE_NAME));
            }

            return false;
        }

        if ($this->isFaking()) {
            if ($this->debug) {
                Log::debug(sprintf('> %s: Sending fake response', StatamicFormManagerProvider::PACKAGE_NAME));
            }

            return $this->getFakeResponse();
        }

        return $this->makeRequest($prepped_data);
    }

    /**
     * @throws \Exception
     */
    protected static function validateData(array $data): bool
    {
        $validator = Validator::make($data, static::rules());

        if ($validator->fails()) {
            throw new \Exception(sprintf(
                '%s: Validation failed for: %s',
                StatamicFormManagerProvider::PACKAGE_NAME,
                $validator->errors()->toJson()
            ));
        }

        return true;
    }

    protected function shouldSend(array $form_data): bool
    {
        if (!isset($this->gate)) {
            return true;
        }

        $gate = $this->gate;

        // Gate can be on eof the following:
        // 1. callback
        // 2. Gate class
        if (class_exists($this->gate) && is_a($this->gate, FormGate::class, true)) {
            return (new $gate)->handle($form_data);
        } else if (is_callable($this->gate)) {
            return $gate($form_data);
        }

        return true;
    }

    public function mappedData(array $form_data): array
    {
        $data = [];

        collect($form_data)->each(function ($value, $key) use (&$data, $form_data) {
            if (!array_key_exists($key, $this->maps)) {
                return true;
            }

            $map_key = $this->maps[$key];

            if (is_array($map_key)) {
                $config      = $map_key;
                $map_key     = $config[0];
                $transformer = $config[1];

                if (class_exists($transformer) && is_a($transformer, FormFieldTransformer::class, true)) {
                    $value = (new $transformer)->handle($key, $value, $form_data);
                } else if (is_callable($transformer)) {
                    $value = $transformer($key, $value, $form_data);
                } else {
                    throw new MissingFormFieldTransformerException(sprintf(
                        '> %s: Transformer not found for [%s]',
                        StatamicFormManagerProvider::PACKAGE_NAME,
                        $key
                    ));
                }
            }

            $data[$map_key] = $value;

            return true;
        });

        // Loop through each computed and add to data
        collect($this->computed)->each(function ($value, $key) use (&$data, $form_data) {
            if (class_exists($value) && is_a($value, FormFieldTransformer::class, true)) {
                $value = (new $value)->handle(key: $key, value: null, form_data: $form_data);
            } else if (is_callable($key)) {
                $value = $key(key: $key, value: null, form_data: $form_data);
            } else {
                throw new MissingFormFieldTransformerException(sprintf(
                    '> %s: Transformer not found for [%s]',
                    StatamicFormManagerProvider::PACKAGE_NAME,
                    $key
                ));
            }

            $data[$key] = $value;
        });

        if (!empty($this->defaults)) {
            $data = array_merge($this->defaults, $data);
        }

        if($this->debug) {
            Log::debug(sprintf('> %s: Form Data:', StatamicFormManagerProvider::PACKAGE_NAME));
            Log::debug(json_encode($data));
        }

        return $data;
    }
}