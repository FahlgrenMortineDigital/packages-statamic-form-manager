<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Exception;
use Fahlgrendigital\StatamicFormManager\Managers\TransactionalFormManager;
use Fahlgrendigital\StatamicFormManager\Support\ManagerFactory;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\MailableStub;
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

        $form_config = new ManagerFactory();

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

        $form_config = new ManagerFactory();

        $this->assertCount(0, $form_config->get('test_form'));
    }

    public function test_catch_missing_url_exception_for_crm()
    {
        Config::set('statamic-forms.forms.test_form', [
            'crm::sales-force' => [
                '::fake'      => false,
                '::enabled'   => true,
                '::fake-type' => 'success',
                'default'     => [
                    'lead_source' => fake()->company,
                ],
                'maps'        => [
                    'crm_1' => 'email'
                ]
            ]
        ]);

        $form_config = new ManagerFactory();

        $this->assertThrows(function () use ($form_config) {
            $form_config->get('test_form');
        }, Exception::class);
    }

    public function test_local_url_overrides_global_url_for_crm_manager()
    {
        Config::set('statamic-forms.forms.test_form', [
            'crm::sales-force' => [
                '::fake'      => false,
                '::enabled'   => true,
                '::fake-type' => 'success',
                '::url'       => fake()->url,
                'default'     => [
                    'lead_source' => fake()->company,
                ],
                'maps'        => [
                    'crm_1' => 'email'
                ]
            ]
        ]);

        Config::set('statamic-forms.defaults', [
            'crm::sales-force' => [
                '::url' => fake()->url
            ]
        ]);

        $form_config = new ManagerFactory();
        $managers    = $form_config->get('test_form');
        $crm_manager = $managers->first();

        $this->assertEquals(
            $crm_manager->url,
            Config::get('statamic-forms.forms.test_form.crm::sales-force.::url')
        );
    }

    public function test_global_url_with_no_local_crm_url()
    {
        Config::set('statamic-forms.forms.test_form', [
            'crm::sales-force' => [
                '::fake'      => false,
                '::enabled'   => true,
                '::fake-type' => 'success',
                'default'     => [
                    'lead_source' => fake()->company,
                ]
            ]
        ]);

        Config::set('statamic-forms.defaults', [
            'crm::sales-force' => [
                '::url' => fake()->url
            ]
        ]);

        $form_config = new ManagerFactory();
        $managers    = $form_config->get('test_form');
        $crm_manager = $managers->first();

        $this->assertEquals(
            $crm_manager->url,
            Config::get('statamic-forms.defaults.crm::sales-force.::url')
        );
    }

    public function test_catch_missing_mailable_exception_for_transactional()
    {
        Config::set('statamic-forms.forms.test_form', [
            'transactional::' => [
                '::enabled' => true,
                '::fake'    => false,
                'mailto'    => [
                    'caps001@columbusplasticsurgery.com'
                ],
            ]
        ]);

        $form_config = new ManagerFactory();

        $this->assertThrows(function () use ($form_config) {
            $form_config->get('test_form');
        }, Exception::class);
    }

    public function test_catch_null_mailable_exception_for_transactional()
    {
        Config::set('statamic-forms.forms.test_form', [
            'transactional' => [
                '::enabled' => true,
                '::fake'    => false,
                'mailable'  => null,
                'mailto'    => [
                    'caps001@columbusplasticsurgery.com'
                ],
            ]
        ]);

        $form_config = new ManagerFactory();

        $this->assertThrows(function () use ($form_config) {
            $form_config->get('test_form');
        }, Exception::class);
    }

    public function test_mailable_is_a_valid_class()
    {
        Config::set('statamic-forms.forms.test_form', [
            'transactional' => [
                '::enabled' => true,
                '::fake'    => false,
                'mailable'  => 'RandomClass\\Path\\Goes\\Here',
                'mailto'    => [
                    'caps001@columbusplasticsurgery.com'
                ],
            ]
        ]);

        $form_config = new ManagerFactory();

        $this->assertThrows(function () use ($form_config) {
            $form_config->get('test_form');
        }, Exception::class);
    }

    public function test_mailto_is_not_empty()
    {
        Config::set('statamic-forms.forms.test_form', [
            'transactional' => [
                '::enabled' => true,
                '::fake'    => false,
                'mailable'  => 'RandomClass\\Path\\Goes\\Here',
                'mailto'    => [fake()->email],
            ]
        ]);

        $form_config = new ManagerFactory();

        $this->assertThrows(function () use ($form_config) {
            $form_config->get('test_form');
        }, Exception::class);
    }

    public function test_mailto_local_overrides_global()
    {
        Config::set('statamic-forms.forms.test_form', [
            'transactional' => [
                '::enabled' => true,
                '::fake'    => false,
                'mailto'    => [fake()->email],
                'mailable'  => MailableStub::class,
            ]
        ]);

        Config::set('statamic-forms.default', [
            'transactional' => [
                'mailto' => [fake()->email()]
            ]
        ]);

        $form_config = new ManagerFactory();
        /** @var TransactionalFormManager $manager */
        $manager = $form_config->get('test_form')->first();

        $this->assertEquals(
            Config::get('statamic-forms.forms.test_form.transactional.mailto'),
            $manager->recipients
        );
    }
}