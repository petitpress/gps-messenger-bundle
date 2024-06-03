UPGRADE FROM 2.x to 3.0
=======================

## Upgrade to Google PubSub V2

`GpsMessengerBundle` `3.0` requires `google/cloud-pubsub` version `2`. It has many BC breaks in the configuration options.

For example, instead of `keyFile`:

```yaml
# config/packages/messenger.yaml

framework:
    messenger:
        transports:
            gps_transport:
                dsn: 'gps://default'
                options:
                    client_config:
                        keyFile: '%env(json:base64:GOOGLE_PUBSUB_KEY)%'
```

you have to use `credentials`:

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

For all other BC breaks, please read: https://github.com/googleapis/google-cloud-php/blob/2a8108caf5065132bb41872cab8a668b5d4e6bdc/PubSub/MIGRATING.md

## Removed option `queue`

The option `queue` was removed. Please use `subscription` instead.

## Removed option `max_messages_pull`

The option `max_messages_pull` was removed, use option `subscription.pull.maxMessages` instead.
