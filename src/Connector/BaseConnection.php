<?php

namespace Fahlgrendigital\StatamicFormManager\Connector;

use Fahlgrendigital\StatamicFormManager\Contracts\FormFieldTransformer;
use Fahlgrendigital\StatamicFormManager\Contracts\FormGate;
use Fahlgrendigital\StatamicFormManager\Contracts\SubmissionInterface;
use Fahlgrendigital\StatamicFormManager\Exceptions\MissingFormFieldTransformerException;
use Fahlgrendigital\StatamicFormManager\Connector\Traits\CanFake;
use Fahlgrendigital\StatamicFormManager\StatamicFormidableFormDataProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

abstract class BaseConnection
{
    use CanFake;

    protected string $gate;
    protected bool $debug = false;
    protected ?string $handle = null;

    # local => remote
    protected array $maps = [];

    protected array $computed = [];

    protected array $defaults = [];

    abstract protected function makeRequest(array $data): ConnectorResponse;

    abstract protected function prepData(SubmissionInterface $submission): array;

    abstract public function logPayload(SubmissionInterface $submission): bool;

    abstract public static function rules(): array;

    public function debug(bool $mode): self
    {
        $this->debug = $mode;

        return $this;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function registerFormGate($gate): self
    {
        $this->gate = $gate;

        return $this;
    }

    public function send(SubmissionInterface $submission): ConnectorResponse
    {
        $prepped_data = $this->prepData($submission);

        if ($this->debug) {
            Log::debug(sprintf('> %s: %s', StatamicFormidableFormDataProvider::PACKAGE_NAME, json_encode($prepped_data)));
        }

        if (!$this->shouldSend($submission->toArray())) {
            if ($this->debug) {
                Log::debug(sprintf('> %s: CRM gate failed', StatamicFormidableFormDataProvider::PACKAGE_NAME));
            }

            return (new ConnectorResponse())->setPassState(false);
        }

        if ($this->isFaking()) {
            if ($this->debug) {
                Log::debug(sprintf('> %s: Sending fake response', StatamicFormidableFormDataProvider::PACKAGE_NAME));
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
                StatamicFormidableFormDataProvider::PACKAGE_NAME,
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
                        StatamicFormidableFormDataProvider::PACKAGE_NAME,
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
                    StatamicFormidableFormDataProvider::PACKAGE_NAME,
                    $key
                ));
            }

            $data[$key] = $value;
        });

        if (!empty($this->defaults)) {
            $data = array_merge($this->defaults, $data);
        }

        return $data;
    }
}