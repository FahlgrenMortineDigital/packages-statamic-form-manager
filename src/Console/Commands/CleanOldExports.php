<?php

namespace Fahlgrendigital\StatamicFormManager\Console\Commands;

use Fahlgrendigital\StatamicFormManager\Data\Export;
use Illuminate\Console\Command;

class CleanOldExports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'formidable:clean-exports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean exports older than the configured expiration window';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $expiration_value = config('statamic-formidable.exports.expiration');
        $expiration_unit  = config('statamic-formidable.exports.expiration_unit');

        $exports = Export::where('created_at', '<', now()->sub($expiration_unit, $expiration_value))->get();

        $exports->each->delete();

        $this->info('Old exports have been cleaned up!');
    }
}