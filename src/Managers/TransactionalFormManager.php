<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Closure;
use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Fahlgrendigital\StatamicFormManager\Support\FormConfig;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Statamic\Forms\Submission;

class TransactionalFormManager extends BaseManager implements FormManager
{
    protected string $mailable;
    public array $recipients;

    public static function init(string $key, array $config, ?string $subtype = null): FormManager
    {
        $form_config = new FormConfig($key, $config, $subtype);
        $mailto      = $form_config->value('mailto');
        $mailable    = $form_config->value('mailable');

        Validator::make([
            'mailto'   => $mailto,
            'mailable' => $mailable
        ], static::rules())->validate();

        $instance             = new self;
        $instance->mailable   = $mailable;
        $instance->recipients = is_array($mailto) ? $mailto : Arr::wrap($mailto);

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
            'mailable' => ['required', function (string $attribute, mixed $value, Closure $fail) {
                if (!class_exists($value)) {
                    $fail(sprintf("The %s class does not exist.", $value));
                }
            }]
        ];
    }

    protected function makeRequest(array $data): bool
    {
        foreach ($this->recipients as $recipient) {
            $mailable = (new $this->mailable(collect($data)))
                ->onConnection(config('statamic-form-manager.queue.connection'))
                ->onQueue(config('statamic-form-manager.queue.queue'));

            Mail::to($recipient)->queue($mailable);
        }

        return true;
    }
}