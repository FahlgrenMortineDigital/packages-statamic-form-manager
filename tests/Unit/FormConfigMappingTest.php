<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Unit;

use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\Exceptions\MissingFormFieldTransformerException;
use Fahlgrendigital\StatamicFormManager\Connector\BaseConnection;
use Fahlgrendigital\StatamicFormManager\Connector\ConnectionFactory;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\BadTransformer;
use Fahlgrendigital\StatamicFormManager\Tests\Stubs\GoodTransformer;
use Fahlgrendigital\StatamicFormManager\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class FormConfigMappingTest extends TestCase
{
    public function test_fails_maps_computed_values_for_rest_api_manager()
    {
        Config::set('statamic-formidable-forms.forms.test_form', [
            'http::test-1' => [
                '::enabled'   => true,
                '::fake'      => false,
                '::fake-type' => 'success',
                '::url'       => 'https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8',
                'default'     => [
                    'lead_source' => fake()->company,
                ],
                'computed'    => [
                    'computed_1' => BadTransformer::class
                ]
            ]
        ]);

        $this->assertThrows(function () {
            $form_config = new ConnectionFactory();
            /** @var ConnectorContract|BaseConnection $manager */
            $manager = $form_config->getConnectors('test_form')->first();
            $manager->mappedData(['crm_1' => fake()->name]);
        }, MissingFormFieldTransformerException::class);
    }

    public function test_maps_computed_values_correctly_for_rest_api_manager()
    {
        Config::set('statamic-formidable-forms.forms.test_form', [
            'http::test-1' => [
                '::enabled'   => true,
                '::fake'      => false,
                '::fake-type' => 'success',
                '::url'       => 'https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8',
                'default'     => [
                    'default_value' => fake()->company,
                ],
                'maps'        => [
                    'email' => ['mapped_email', GoodTransformer::class]
                ],
                'computed'    => [
                    'computed_1' => GoodTransformer::class
                ]
            ]
        ]);

        $form_config = new ConnectionFactory();
        /** @var ConnectorContract|BaseConnection $manager */
        $manager     = $form_config->getConnectors('test_form')->first();
        $submission  = [
            'email' => fake()->email
        ];
        $mapped_data = $manager->mappedData($submission);

        $this->assertArrayHasKey('computed_1', $mapped_data);

        $this->assertEquals('test', $mapped_data['computed_1']);
    }

    public function test_maps_normal_value_correctly_for_rest_api_manager()
    {
        Config::set('statamic-formidable-forms.forms.test_form', [
            'http::test-1' => [
                '::enabled'   => true,
                '::fake'      => false,
                '::fake-type' => 'success',
                '::url'       => 'https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8',
                'default'     => [
                    'default_value' => fake()->company,
                ],
                'maps'        => [
                    'email' => ['mapped_email', GoodTransformer::class]
                ],
                'computed'    => [
                    'computed_1' => GoodTransformer::class
                ]
            ]
        ]);

        $form_config = new ConnectionFactory();
        /** @var ConnectorContract|BaseConnection $manager */
        $manager     = $form_config->getConnectors('test_form')->first();
        $submission  = [
            'email' => fake()->email
        ];
        $mapped_data = $manager->mappedData($submission);

        $this->assertArrayHasKey('mapped_email', $mapped_data);

        $this->assertEquals('test', $mapped_data['mapped_email']);
    }

    public function test_fails_maps_values_for_rest_api_manager()
    {
        Config::set('statamic-formidable-forms.forms.test_form', [
            'http::test-1' => [
                '::enabled'   => true,
                '::fake'      => false,
                '::fake-type' => 'success',
                '::url'       => 'https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8',
                'default'     => [
                    'lead_source' => fake()->company,
                ],
                'maps'        => [
                    'crm_1' => ['email', BadTransformer::class]
                ]
            ]
        ]);

        $this->assertThrows(function () {
            $form_config = new ConnectionFactory();
            /** @var ConnectorContract|BaseConnection $manager */
            $manager = $form_config->getConnectors('test_form')->first();
            $manager->mappedData(['crm_1' => fake()->name]);
        }, MissingFormFieldTransformerException::class);
    }
}