<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Closure;
use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Fahlgrendigital\StatamicFormManager\Managers\Traits\CanFake;
use Fahlgrendigital\StatamicFormManager\Support\FormConfig;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Statamic\Forms\Submission;

class TransactionalFormManager extends BaseManager implements FormManager
{
    use CanFake;

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

    public function send(Submission $submission): bool
    {
        $prepped_data = $this->prepData($submission);

        if ($this->debug) {
            Log::debug(json_encode($prepped_data));
        }

        if (!$this->shouldSend($prepped_data)) {
            return false;
        }

        foreach ($this->recipients as $recipient) {
            $mailable = (new $this->mailable($prepped_data))
                ->onConnection(config('statamic-form-manager.queue.connection'))
                ->onQueue(config('statamic-form-manager.queue.queue'));

            Mail::to($recipient)->queue($mailable);
        }

        return true;
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
}