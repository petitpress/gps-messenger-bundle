<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationResolver;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationResolverInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsTransport;
use PetitPress\GpsMessengerBundle\Transport\GpsTransportFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class GpsTransportFactoryTest extends TestCase
{
    #[DataProvider('dsnProvider')]
    public function testSupports(bool $expected, string $dsn): void
    {
        $factory = new GpsTransportFactory(
            $this->createMock(GpsConfigurationResolverInterface::class),
            $this->createMock(CacheItemPoolInterface::class),
            null
        );

        static::assertSame($expected, $factory->supports($dsn, []));
    }

    public function testCreateTransport(): void
    {
        $factory = new GpsTransportFactory(
            new GpsConfigurationResolver(),
            $this->createMock(CacheItemPoolInterface::class),
            null
        );

        $transport = $factory->createTransport(
            'gps://default',
            [
                'client_config' => [
                    'projectId' => 'emulator-project',
                    'credentials' => json_decode(
                        '{"client_id":"emulator","client_secret":"emulator","refresh_token":"emulator","type":"authorized_user"}',
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    ),
                ],
            ],
            $this->createMock(SerializerInterface::class)
        );

        /** @phpstan-ignore-next-line staticMethod.alreadyNarrowedType */
        static::assertInstanceOf(GpsTransport::class, $transport);
    }

    /**
     * @return array<array-key, array{bool, string}>
     */
    public static function dsnProvider(): array
    {
        return [
            [true,  'gps://defaults/messages?client_config[apiEndpoint]=0.0.0.0:8432'],
            [false, 'http://0.0.0.0:8080'],
            [false, 'https://0.0.0.0:8080'],
            [false, '0.0.0.0:8080'],
        ];
    }
}
