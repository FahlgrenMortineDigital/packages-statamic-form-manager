<?php

namespace Fahlgrendigital\StatamicFormManager\Connector;

use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\Contracts\MailableConnector as MailableConnectorContract;
use Fahlgrendigital\StatamicFormManager\Support\FormConfig;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Statamic\Forms\Submission;

class MailableConnection extends BaseConnection implements MailableConnectorContract, ConnectorContract
{
    protected string $mailable;
    protected array $recipients;

    /**
     * @throws \Exception
     */
    public static function init(string $key, array $config, ?string $subtype = null): ConnectorContract
    {
        $form_config = new FormConfig($key, $config, $subtype);
        $mailto      = $form_config->value('mailto');
        $mailable    = $form_config->value('mailable');

        static::validateData([
            'mailto'   => $mailto,
            'mailable' => $mailable
        ]);

        $instance         = new self;
        $instance->handle = $form_config->key();
        $instance->setMailable($mailable);
        $instance->setRecipients(is_array($mailto) ? $mailto : Arr::wrap($mailto));

        if ($form_config->localValue('::gate')) {
            $instance->registerFormGate($form_config->localValue('::gate'));
        }

        return $instance;
    }

    # Prep submission data for form
    protected function prepData(Submission $submission): array
    {
        return $submission->toArray();
    }

    public static function rules(): array
    {
        return [
            'mailto'   => ['required'],
            'mailable' => ['required', function (string $attribute, mixed $value, \Closure $fail) {
                if (!class_exists($value)) {
                    $fail(sprintf("The %s class does not exist.", $value));
                }
            }]
        ];
    }

    protected function makeRequest(array $data): bool
    {
        foreach ($this->recipients as $recipient) {
            $mailable = (new $this->mailable($data))
                ->onConnection(config('statamic-form-manager.queue.connection'))
                ->onQueue(config('statamic-form-manager.queue.queue'));

            Mail::to($recipient)->queue($mailable);
        }

        return true;
    }

    public function setRecipients(array $recipients): void
    {
        $this->recipients = $recipients;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function getMailable(): string
    {
        return $this->mailable;
    }

    public function setMailable(string $mailable_class): void
    {
        $this->mailable = $mailable_class;
    }
}