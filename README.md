Google Pub/Sub transport implementation for Symfony Messenger
========

This bundle provides a simple implementation of Google Pub/Sub transport for Symfony Messenger.

The bundle requires only `symfony/messenger` and `symfony/options-resolver` packages. 
In contrast with [Enqueue GPS transport](https://github.com/php-enqueue/gps),
it doesn't require [Enqueue](https://github.com/php-enqueue), 
and [some bridge](https://github.com/sroze/messenger-enqueue-transport#readme). 
It supports ordering messages by `OrderingKeyStamp` and it's not outdated. 

## Installation

### Step 1: Install the Bundle

From within container execute the following command to download the latest version of the bundle:

```console
$ composer require petitpress/gps-messenger-bundle --no-scripts
```

### Step 2: Configure environment variables

Official [Google Cloud PubSub SDK](https://github.com/googleapis/google-cloud-php-pubsub) 
requires some globally accessible environment variables.

You might need to change default Symfony DotEnv instance to use `putenv` 
as Google needs to access some variables through `getenv`. To do so, use putenv method in `config/bootstrap.php`:
```php
(new Dotenv())->usePutenv()->...
```

List of Google Pub/Sub configurable variables :
```dotenv
# use these for production environemnt:
GOOGLE_APPLICATION_CREDENTIALS='google-pubsub-credentials.json'
GCLOUD_PROJECT='project-id'

# use these for development environemnt (if you have installed Pub/Sub emulator):
PUBSUB_EMULATOR_HOST=http://localhost:8538
```

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