Google Pub/Sub transport implementation for Symfony Messenger
========

This bundle provides a simple implementation of Google Pub/Sub transport for Symfony Messenger.

The bundle requires only `symfony/messenger`, `google/cloud-pubsub` and `symfony/options-resolver` packages. 
In contrast with [Enqueue GPS transport](https://github.com/php-enqueue/gps),
it doesn't require [Enqueue](https://github.com/php-enqueue) 
and [some bridge](https://github.com/sroze/messenger-enqueue-transport#readme). 
It supports ordering messages with `OrderingKeyStamp` and it's not outdated. 

## Installation

### Step 1: Install the Bundle

From within container execute the following command to download the latest version of the bundle:

```console
$ composer require petitpress/gps-messenger-bundle --no-scripts
```

### Step 2: Configure environment variables

Official [Google Cloud PubSub SDK](https://github.com/googleapis/google-cloud-php-pubsub) 
requires some globally accessible environment variables.

If you want to provide the PubSub authentication info through environment variables
you might need to change default Symfony DotEnv instance to use `putenv` 
as Google needs to access some variables through `getenv`. To do so, use putenv method in `config/bootstrap.php` 
(does no longer exist in Symfony 5.3 and above):
```php
(new Dotenv())->usePutenv()->...
```

List of Google Pub/Sub configurable variables :
```dotenv
# use these for production environemnt:
GOOGLE_APPLICATION_CREDENTIALS='google-pubsub-credentials.json'
GOOGLE_CLOUD_PROJECT='project-id'

# use these for development environemnt (if you have installed Pub/Sub emulator):
PUBSUB_EMULATOR_HOST=http://localhost:8538
```

If you want to use the bundle with Symfony Version 5.3 and above you need to configure those variables 
inside the `config/packages/messenger.yaml`.

### Step 3: Configure Symfony Messenger
```yaml
# config/packages/messenger.yaml

framework:
    messenger:
        transports:
            gps_transport:
                dsn: 'gps://default'
                options:
                    max_messages_pull: 10 # optional (default: 10)
                    topic: # optional (default name: messages)
                        name: 'messages'
                    queue: # optional (default the same as topic.name)
                        name: 'messages'
                        
                    # optional (see google-cloud-php-pubsub documentation on GOOGLE_APPLICATION_CREDENTIALS)
                    keyFilePath: '%env(GOOGLE_APPLICATION_CREDENTIALS)%'
                    # optional (see google-cloud-php-pubsub documentation on PUBSUB_EMULATOR_HOST)
                    emulatorHost: '%env(PUBSUB_EMULATOR_HOST)%'
                    # mandatory (see google-cloud-php-pubsub documentation on GOOGLE_CLOUD_PROJECT)
                    projectId: '%env(GOOGLE_CLOUD_PROJECT)%'
```
or:
```yaml
# config/packages/messenger.yaml

framework:
    messenger:
        transports:
            gps_transport:
                dsn: 'gps://default/messages?max_messages_pull=10'
```

### Step 4: Use available stamps if needed

* `OrderingKeyStamp`: use for keeping messages of the same context in order. 
  For more information, read an [official documentation](https://cloud.google.com/pubsub/docs/publisher#using_ordering_keys). 
