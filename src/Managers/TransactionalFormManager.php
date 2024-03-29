<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Fahlgrendigital\StatamicFormManager\Exceptions\MissingManagerConfigParamException;
use Fahlgrendigital\StatamicFormManager\Exceptions\MissingManagerMailableException;
use Fahlgrendigital\StatamicFormManager\Managers\Traits\CanFake;
use Fahlgrendigital\StatamicFormManager\Managers\Traits\Subtypeable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Statamic\Forms\Submission;

class TransactionalFormManager extends BaseManager implements FormManager
{
    use CanFake;
    use Subtypeable;

    protected string $mailable;
    public array $recipients;

    public static function make(string $mailable, array $recipients): self
    {
        $manager             = new self;
        $manager->mailable   = $mailable;
        $manager->recipients = $recipients;

        return $manager;
    }

    public static function init(string $key, array $config, ?string $subtype = null): FormManager
    {
        if (!array_key_exists('mailto', $config) || empty($config['mailto'])) {
            throw new MissingManagerConfigParamException("Missing required config param [mailto]");
        }

        if (!array_key_exists('mailable', $config) || empty($config['mailable'])) {
            throw new MissingManagerConfigParamException("Missing required config param [mailable]");
        }

        if (!class_exists($config['mailable'])) {
            throw new MissingManagerMailableException("{$config['mailable']} could not be found.");
        }

        $global_key    = static::buildConfigKey($key, $subtype);
        $global_config = config(sprintf("statamic-forms.defaults.%s", $global_key), []);
        $global_mailto = Arr::get($global_config, 'mailto', []);
        $local_mailto  = Arr::get($config, 'mailto', []);

        if (is_string($global_mailto)) {
            $global_mailto = [$global_mailto];
        }

        if (is_string($local_mailto)) {
            $local_mailto = [$local_mailto];
        }

        $mailto = array_merge($global_mailto, $local_mailto);

        if (empty($mailto)) {
            throw new MissingManagerConfigParamException("Missing required config param [mailto]");
        }

        $instance = static::make(
            Arr::get($config, 'mailable'),
            $mailto
        );

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
}