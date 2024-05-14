<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Google\Cloud\PubSub\PubSubClient;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 * @implements TransportFactoryInterface<GpsTransport>
 */
final class GpsTransportFactory implements TransportFactoryInterface
{
    private GpsConfigurationResolverInterface $gpsConfigurationResolver;
    private ?CacheItemPoolInterface $cache;

    public function __construct(GpsConfigurationResolverInterface $gpsConfigurationResolver, ?CacheItemPoolInterface $cache)
    {
        $this->gpsConfigurationResolver = $gpsConfigurationResolver;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $options = $this->gpsConfigurationResolver->resolve($dsn, $options);

        $clientConfig = $options->getClientConfig();
        if ($this->cache instanceof CacheItemPoolInterface) {
            $clientConfig['credentialsConfig']['authCache'] ??= $this->cache;
        }

        return new GpsTransport(
            new PubSubClient($clientConfig),
            $options,
            $serializer
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'gps://');
    }
}
