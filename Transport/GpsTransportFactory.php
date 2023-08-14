<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Google\Cloud\PubSub\PubSubClient;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsTransportFactory implements TransportFactoryInterface
{
    private GpsConfigurationResolverInterface $gpsConfigurationResolver;
    private ?CacheItemPoolInterface $cache;
    private SerializerInterface $serializer;
    private ?LoggerInterface $logger;

    public function __construct(
        GpsConfigurationResolverInterface $gpsConfigurationResolver,
        ?CacheItemPoolInterface $cache,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->gpsConfigurationResolver = $gpsConfigurationResolver;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function createTransport(string $dsn, array $options): TransportInterface
    {
        $options = $this->gpsConfigurationResolver->resolve($dsn, $options);

        $clientConfig = $options->getClientConfig();
        if ($this->cache instanceof CacheItemPoolInterface) {
            $clientConfig['authCache'] ??= $this->cache;
        }

        return new GpsTransport(
            new PubSubClient($clientConfig),
            $options,
            $this->serializer,
            $this->logger
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
