<?php

namespace Fahlgrendigital\StatamicFormManager\Managers;

use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Fahlgrendigital\StatamicFormManager\Managers\Traits\CanFake;
use Fahlgrendigital\StatamicFormManager\Managers\Traits\Subtypeable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Statamic\Forms\Submission;

class TransactionalFormManager implements FormManager
{
    use CanFake;
    use Subtypeable;

    protected string $mailable;
    protected array $recipients;

    public static function make(string $mailable, array $recipients): self
    {
        $manager             = new self;
        $manager->mailable   = $mailable;
        $manager->recipients = $recipients;

        return $manager;
    }

    public static function init(string $key, array $config, ?string $subtype = null): FormManager
    {
        $global_key    = static::buildConfigKey($key, $subtype);
        $global_config = config(sprintf("form-mappings.defaults.%s", $global_key), []);
        $global_mailto = Arr::get($global_config, 'mailto', []);
        $local_mailto  = Arr::get($config, 'mailto', []);

        if (is_string($global_mailto)) {
            $global_mailto = [$global_mailto];
        }

        if (is_string($local_mailto)) {
            $local_mailto = [$local_mailto];
        }

        $mailto = array_merge($global_mailto, $local_mailto);

        return static::make(
            Arr::get($config, 'mailable'),
            $mailto
        );
    }

    public function send(Submission $submission): bool
    {
        $prepped_data = collect($this->prepData($submission));

        foreach ($this->recipients as $recipient) {
            Mail::to($recipient)->send(new $this->mailable($prepped_data));
        }

        return true;
    }

    # Prep submission data for form
    protected function prepData(Submission $submission): array
    {
        return $submission->toArray();
    }
}