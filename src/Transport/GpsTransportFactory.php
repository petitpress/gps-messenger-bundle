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
    private ?string $forcedTransport;

    public function __construct(
        GpsConfigurationResolverInterface $gpsConfigurationResolver,
        ?CacheItemPoolInterface $cache,
        ?string $forcedTransport
    ) {
        $this->gpsConfigurationResolver = $gpsConfigurationResolver;
        $this->cache = $cache;
        $this->forcedTransport = $forcedTransport;
    }

    /**
     * @param array<mixed> $options
     *
     * @return GpsTransport
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $options = $this->gpsConfigurationResolver->resolve($dsn, $options);

        $clientConfig = $options->getClientConfig();
        if ($this->cache instanceof CacheItemPoolInterface) {
            if (! is_array($clientConfig['credentialsConfig'])) {
                $clientConfig['credentialsConfig'] = [];
            }
            $clientConfig['credentialsConfig']['authCache'] ??= $this->cache;
        }
        if (isset($this->forcedTransport)) {
            $clientConfig['transport'] = $this->forcedTransport;
        }

        return new GpsTransport(
            new PubSubClient($clientConfig),
            $options,
            $serializer
        );
    }

    /**
     * @param array<mixed> $options
     */
    public function supports(string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'gps://');
    }
}
