<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Exceptions\MissingManagerConfigParamException;
use Fahlgrendigital\StatamicFormManager\Support\FormConfig;
use Illuminate\Support\Facades\Config;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;

class FormConfigTest extends TestCase
{
    public function test_skips_disabled_configs_with_enabled_false()
    {
        Config::set('statamic-forms.forms.test_form', [
            'crm::sales-force'    => [
                '::enabled'   => true,
                '::fake'      => false,
                '::fake-type' => 'success',
                '::url'       => 'https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8',
                'default'     => [
                    'lead_source' => fake()->company,
                ],
                'maps'        => [
                    'crm_1' => 'email'
                ]
            ],
            'pardot::sales-force' => [
                '::enabled'   => false,
                '::fake'      => false,
                '::fake-type' => 'success',
                '::url'       => 'https://go.pardot.com/l/859683/2020-06-04/6gxc',
                'maps'        => [
                    'input_1' => 'email',
                    'input_2' => 'first_name',
                    'input_3' => 'last_name',
                ]
            ]
        ]);

        $form_config = new FormConfig();

        $this->assertCount(1, $form_config->get('test_form'));
    }

    public function test_missing_enabled_skips_form_config()
    {
        Config::set('statamic-forms.forms.test_form', [
            'crm::sales-force' => [
                '::fake'      => false,
                '::fake-type' => 'success',
                '::url'       => 'https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8',
                'default'     => [
                    'lead_source' => fake()->company,
                ],
                'maps'        => [
                    'crm_1' => 'email'
                ]
            ]
        ]);

        $form_config = new FormConfig();

        $this->assertCount(0, $form_config->get('test_form'));
    }

    public function test_catch_missing_url_exception()
    {
        Config::set('statamic-forms.forms.test_form', [
            'crm::sales-force' => [
                '::fake'      => false,
                '::fake-type' => 'success',
                'default'     => [
                    'lead_source' => fake()->company,
                ],
                'maps'        => [
                    'crm_1' => 'email'
                ]
            ]
        ]);

        $form_config = new FormConfig();

        //todo[aclinton] - this is not working
        $this->assertThrows(function () use($form_config) {
            $form_config->get('test_form');
        }, MissingManagerConfigParamException::class);
    }
}