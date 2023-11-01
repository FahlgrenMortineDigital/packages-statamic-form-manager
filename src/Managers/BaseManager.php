<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Statamic\Forms\Submission;

abstract class BaseManager
{
    protected $gate;
    protected bool $debug = false;

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
}