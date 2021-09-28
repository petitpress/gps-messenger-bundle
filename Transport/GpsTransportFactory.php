<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Google\Cloud\PubSub\PubSubClient;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsTransportFactory implements TransportFactoryInterface
{
    const GOOGLE_APPLICATION_CREDENTIALS = 'GOOGLE_APPLICATION_CREDENTIALS';
    const GOOGLE_CLOUD_PROJECT = 'GOOGLE_CLOUD_PROJECT';
    const PUBSUB_EMULATOR_HOST = 'PUBSUB_EMULATOR_HOST';

    private GpsConfigurationResolverInterface $gpsConfigurationResolver;

    public function __construct(GpsConfigurationResolverInterface $gpsConfigurationResolver)
    {
        $this->gpsConfigurationResolver = $gpsConfigurationResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $this->resolvePubSubEnvOptions($options);

        return new GpsTransport(
            new PubSubClient(),
            $this->gpsConfigurationResolver->resolve($dsn, $options),
            $serializer
        );
    }

    protected function resolvePubSubEnvOptions(array &$options): void
    {
        $envMap = [
            'projectId' => self::GOOGLE_CLOUD_PROJECT,
            'emulatorHost' => self::PUBSUB_EMULATOR_HOST,
            'keyFilePath' => self::GOOGLE_APPLICATION_CREDENTIALS
        ];

        foreach ($envMap as $optKey => $envKey) {
            if (array_key_exists($optKey, $options)) {
                if (!empty($options[$optKey])) {
                    putenv($envKey . '=' . $options[$optKey]);
                }
                unset($options[$optKey]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'gps://');
    }
}
