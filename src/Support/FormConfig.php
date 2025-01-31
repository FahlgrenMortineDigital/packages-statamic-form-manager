<?php

namespace Fahlgrendigital\StatamicFormManager\Support;

class FormConfig
{
    protected array $config;

    public function __construct(protected string $form_handle, protected string $type, protected ?string $subtype)
    {
        $this->config = config(sprintf("statamic-formidable-forms.forms.%s.%s", $form_handle, $this->key()), []);
    }

    public function key(): string
    {
        $global_key = $this->type;

        if (!empty($this->subtype)) {
            $global_key .= "::$this->subtype";
        }

        return $global_key;
    }

    public function localConfig(): array
    {
        return $this->config;
    }

    public function globalConfig(): array
    {
        return config(sprintf("statamic-formidable-forms.defaults.%s", $this->key()), []);
    }

    public function localValue(string $key, mixed $default = null)
    {
        return $this->localConfig()[$key] ?? $default;
    }

    public function value(string $key, mixed $default = null): mixed
    {
        return $this->localValue($key) ?? $this->globalConfig()[$key] ?? $default;
    }

    public function mergeValue(string $key): array
    {
        return array_merge(
            $this->globalConfig()[$key] ?? [],
            $this->localConfig()[$key] ?? []
        );
    }
}