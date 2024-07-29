<?php

namespace Fahlgrendigital\StatamicFormManager\Contracts;

interface MailableConnector
{
    public function setRecipients(array $recipients): void;
    public function getRecipients(): array;
    public function getMailable(): string;
    public function setMailable(string $mailable_class): void;
}