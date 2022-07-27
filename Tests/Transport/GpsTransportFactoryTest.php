<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationResolverInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsTransportFactory;
use PHPUnit\Framework\TestCase;

class GpsTransportFactoryTest extends TestCase
{
    private GpsTransportFactory $subject;
    private GpsConfigurationResolverInterface $gpsConfigurationResolver;

    protected function setUp(): void
    {
        $this->gpsConfigurationResolver = $this->createMock(GpsConfigurationResolverInterface::class);

        $this->subject = new GpsTransportFactory($this->gpsConfigurationResolver);
    }

    /**
     * @dataProvider dsnProvider
     */
    public function testSupports(bool $expected, string $dsn): void
    {
        $this->assertSame($expected, $this->subject->supports($dsn, []));
    }

    public function dsnProvider(): array
    {
        return [
            [true,  'gps://defaults/messages?client_config[apiEndpoint]=0.0.0.0:8432'],
            [false, 'http://0.0.0.0:8080'],
            [false, 'https://0.0.0.0:8080'],
            [false, '0.0.0.0:8080'],
        ];
    }
}
