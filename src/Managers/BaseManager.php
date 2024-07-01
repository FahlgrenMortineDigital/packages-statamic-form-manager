<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Statamic\Forms\Submission;

abstract class BaseManager
{
    protected $gate;
    protected bool $debug = false;

    # CRM => Statamic form field mappings
    protected array $maps = [];

    public function debug(bool $mode)
    {
        $this->debug = $mode;

        return $this;
    }

    public function registerFormGate($gate): self
    {
        $this->gate = $gate;

        return $this;
    }

    abstract protected function prepData(Submission $submission): array;

    abstract public static function rules(): array;

    protected function shouldSend(array $form_data): bool
    {
        if (!isset($this->gate)) {
            return true;
        }

        $gate = $this->gate;

        if (class_exists($this->gate)) {
            return (new $gate)->handle($form_data);
        } else if (is_callable($this->gate)) {
            return $gate($form_data);
        }

        return true;
    }

    protected function mappedData(array $form_data): array
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
}