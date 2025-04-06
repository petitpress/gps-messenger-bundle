Google Pub/Sub transport implementation for Symfony Messenger
========

This bundle provides a simple implementation of Google Pub/Sub transport for Symfony Messenger.

The bundle requires only `symfony/messenger`, `google/cloud-pubsub` and `symfony/options-resolver` packages. 
In contrast with [Enqueue GPS transport](https://github.com/php-enqueue/gps),
it doesn't require [Enqueue](https://github.com/php-enqueue) 
and [some bridge](https://github.com/sroze/messenger-enqueue-transport#readme).

## Features

- **Compatible with the latest `google/cloud-pubsub` 2.***.
- **Zero extra dependencies** beyond core Symfony Messenger and Pub/Sub libraries.
- **Message ordering support** using the `OrderingKeyStamp`.
- **Flexible and extensive configuration**, available via options or DSN (Data Source Name), including `deadLetterPolicy`, `enableMessageOrdering`, `ackDeadlineSeconds`).
- **Automatic Pub/Sub Topic and Subscription creation**, with the ability to disable it if needed.
- **Keep-alive support** for long-running Messenger workers.

## Support

| Version                                                               | Status             | Symfony Versions |
|-----------------------------------------------------------------------|--------------------|------------------|
| [3.x](https://github.com/petitpress/gps-messenger-bundle/tree/3.x)    | Actively Supported | >= 5.4, <=7.1    |
| [4.x](https://github.com/petitpress/gps-messenger-bundle/tree/master) | In development     | >= 7.2           |

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
# use these for production environment:
GOOGLE_APPLICATION_CREDENTIALS='google-pubsub-credentials.json'
GCLOUD_PROJECT='project-id'

# use these for development environment (if you have installed Pub/Sub emulator):
PUBSUB_EMULATOR_HOST=http://localhost:8538
```

or if you have credentials in a base64 encoded env variable:
```yaml
# config/packages/messenger.yaml

framework:
    messenger:
        transports:
            gps_transport:
                dsn: 'gps://default'
                options:
                    client_config:
                        credentials: '%env(json:base64:GOOGLE_PUBSUB_KEY)%'
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
                    client_config: # optional (default: [])
                        apiEndpoint: 'https://europe-west3-pubsub.googleapis.com'
                    topic: # optional (default name: messages)
                        name: 'messages'
                        options: # optional create options if not exists (default: []), for all options take at look at https://cloud.google.com/php/docs/reference/cloud-pubsub/latest/Topic#_Google_Cloud_PubSub_Topic__create__
                            labels:
                                - label1
                                - label2
                    subscription: # optional (default the same as topic.name)
                        name: 'messages'
                        options: # optional create options if not exists (default: []), for all options take a look at https://cloud.google.com/php/docs/reference/cloud-pubsub/latest/Subscription#_Google_Cloud_PubSub_Subscription__create__
                            enableExactlyOnceDelivery: true
                            labels:
                                - label1
                                - label2
                        pull:
                            maxMessages: 10 # optional (default: 10)

```
or:
```yaml
# config/packages/messenger.yaml

framework:
    messenger:
        transports:
            gps_transport:
                dsn: 'gps://default/messages?client_config[apiEndpoint]=https://europe-west3-pubsub.googleapis.com&subscription[pull][maxMessages]=10'
```
to use emulator in local:
```yaml
# config/packages/dev/messenger.yaml

framework:
    messenger:
        transports:
            gps_transport:
                options:
                    client_config:
                        hasEmulator: true
                        emulatorHost: '%env(PUBSUB_EMULATOR_HOST)%'
```


### Step 4: Configure PetitPressGpsMessengerBundle (optional)

Configure the cache service where authentication tokens are stored. The default is `cache.app`.

```yaml
# config/packages/petit_press_gps_messenger.yaml

petit_press_gps_messenger:
    auth_cache: 'cache.app'
```

### Step 5: Use available stamps if needed

* `OrderingKeyStamp`: use for keeping messages of the same context in order. 
  For more information, read an [official documentation](https://cloud.google.com/pubsub/docs/publisher#using_ordering_keys).

* `AttributesStamp`: use to add contextual metadata to serialized messages. 
  For more information, read an [official documentation](https://cloud.google.com/pubsub/docs/publisher#using-attributes). 
  Can be very useful when used together with [subscription filters](https://cloud.google.com/pubsub/docs/subscription-message-filter).

### Step 6: Create topics from config

```bash
bin/console messenger:setup-transports
```

## License

This bundle is released under the [MIT License](LICENSE).
