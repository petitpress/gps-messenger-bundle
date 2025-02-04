<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationResolverInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsTransportFactory;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

class GpsTransportFactoryTest extends TestCase
{
    private GpsTransportFactory $subject;

    protected function setUp(): void
    {
        $this->subject = new GpsTransportFactory(
            $this->createMock(GpsConfigurationResolverInterface::class),
            $this->createMock(CacheItemPoolInterface::class),
            null
        );
    }

    /**
     * @dataProvider dsnProvider
     */
    public function testSupports(bool $expected, string $dsn): void
    {
        static::assertSame($expected, $this->subject->supports($dsn, []));
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
