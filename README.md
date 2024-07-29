# Statamic Form Manager

Send submitted form data to 3rd party services. Possible examples include:

- Sending data off to a CRM for further lead management
- Sending data off to a DB for redundancy

# Installation

```shell
$ composer require fahlgrendigital/packages-statamic-form-manager
```

### Publish

```shell
$ php artisan vendor:publish --tag=statamic-form-manager-config
```

# Configuration

**Event Listener**

The form manager event listener needs to be configured so that this package picks up submissions from Statamic.
To do this, you need to register the FormSubmissionManager in the Laravel EventServiceProvider.

```bash
protected $listen = [
        \Statamic\Events\SubmissionSaved::class => [
            \Fahlgrendigital\StatamicFormManager\Listeners\FormSubmissionsManager::class
        ]
    ];
```

`statamic-form-manager`

Statamic form manager allows you to configure managers to handle the shuttling of form data to specified destinations.
To create your own managers you can edit the `statamic-form-manager.connectors` array. Give your manager a unique snake-case name
and add it to the list of existing managers.

Behind the scenes, form manager process form data with queues. You may specify the queue connection and queue under the 
`statamic-form-manager.queue` configuration.

**Default Values**

* statamic-form-manager.queue.connection: `env('QUEUE_CONNECTION')` or 'sync'
* statamic-form-manager.queue.queue: `env('STATAMIC_FORM_MANAGER_QUEUE')` or 'form-submissions'

> If you are using Horizon, make sure to configure one of our supervisor processes to handle the queue you 
> set in the above config.

## Form Configuration

`statamic-forms`

By default, form manager, is not configured to handle any forms as they will be specific to each project. 
There are two primary sections to this configuration:

- [defaults](#defaults)
- [forms](#forms)

### Defaults

You may specify manager-specific default values which will be applied to all forms being managed by that manager.
For example, if for your specific CRM (Hubspot), all forms should have default form key/value pair:

> lead_source: really-cool-website

This could be added within the `defaults` section under that manager's snake-case name. If using the built-in CRM manager, 
this might look like the following:

```shell
'defaults' => [
  'crm::hubspot' => [
    'defaults' => [
      'lead_source' => 'really-cool-website'
    ] 
  ]
]
```

> Notice the `crm::hubspot` form name. This will be explained below in further detail.

> Notice the `defaults` key under the form manager name.

**In Depth Look**

There are 3 different buckets of form manager data that can be configured:

- Defaults
- Maps

**Defaults**

These would be static values you want passed along to every form handled by a given manager. The example above
is a good use-case for this.

**Maps**

This configuration houses key to key mapping when building the 3rd party payload blob. For example, let's say your form
has a field called `first_name` in statamic, but in your CRM that field needs to map over to `name_1` for all forms. This
can be configured as follows:

```shell
'defaults' => [
  'crm::hubspot' => [
    'defaults' => [
      'lead_source' => 'really-cool-website'
    ],
    'maps' => [
      'name_1' => 'first_name'
    ]
  ]
]
```

### Forms