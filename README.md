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

Statamic form manager allows you to configure managers to handle the shuttling of form data to specified destinations.
To create your own managers you can edit the `statamic-form-manager.managers` array. Give your manager a unique snake-case name
and add it to the list of existing managers.

Behind the scenes, form manager process form data with queues. You may specify the queue connection and queue under the 
`statamic-form-manager.queue` configuration.

