<?php

namespace Transport;

use Google\Cloud\Core\Exception\GoogleException;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationResolverInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsTransport;
use PetitPress\GpsMessengerBundle\Transport\GpsTransportFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class GpsTransportFactoryTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $serializerProphecy;
    private GpsTransportFactory $gpsTransportFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $gpsConfigurtionProphecy = $this->prophesize(GpsConfigurationInterface::class);
        $gpsCongigurationResolverProphecy = $this->prophesize(GpsConfigurationResolverInterface::class);
        $this->serializerProphecy = $this->prophesize(SerializerInterface::class);

        $gpsCongigurationResolverProphecy->resolve(Argument::any(), Argument::any())->willReturn($gpsConfigurtionProphecy->reveal());

        $this->gpsTransportFactory = new GpsTransportFactory($gpsCongigurationResolverProphecy->reveal());
    }

    public function testCreateTransportFailsWithoutProjectId()
    {
        $dsn = 'gps://';
        $options = [];

        static::assertFalse(getenv(GpsTransportFactory::GOOGLE_CLOUD_PROJECT));

        $this->expectException(GoogleException::class);

        $this->gpsTransportFactory->createTransport($dsn, $options, $this->serializerProphecy->reveal());
    }

    public function testCreateTransportWithProjectIdFromEnvironmentVar()
    {
        $dsn = 'gps://';
        $options = [];

        putenv(GpsTransportFactory::GOOGLE_CLOUD_PROJECT . '=' . 'random');

        $transport = $this->gpsTransportFactory->createTransport($dsn, $options, $this->serializerProphecy->reveal());

        static::assertInstanceOf(GpsTransport::class, $transport);
        static::assertEquals('random', getenv(GpsTransportFactory::GOOGLE_CLOUD_PROJECT));
        static::assertFalse(getenv(GpsTransportFactory::GOOGLE_APPLICATION_CREDENTIALS));
        static::assertFalse(getenv(GpsTransportFactory::PUBSUB_EMULATOR_HOST));
    }

    public function testCreateTransportWithProjectIdFromEnvironmentVarAndConfiguration()
    {
        $dsn = 'gps://';
        $options = ['projectId' => 'specfic'];

        putenv(GpsTransportFactory::GOOGLE_CLOUD_PROJECT . '=' . 'random');

        $transport = $this->gpsTransportFactory->createTransport($dsn, $options, $this->serializerProphecy->reveal());

        static::assertInstanceOf(GpsTransport::class, $transport);
        static::assertEquals('specfic', getenv(GpsTransportFactory::GOOGLE_CLOUD_PROJECT));
        static::assertFalse(getenv(GpsTransportFactory::GOOGLE_APPLICATION_CREDENTIALS));
        static::assertFalse(getenv(GpsTransportFactory::PUBSUB_EMULATOR_HOST));
    }

    public function testCreateTransportWithEmulator()
    {
        $dsn = 'gps://';
        $options = [
            'projectId' => 'random',
            'emulatorHost' => 'address://emulator/host',
            'keyFilePath' => __DIR__ . '/../Resources/credentials.json'
        ];

        $transport = $this->gpsTransportFactory->createTransport($dsn, $options, $this->serializerProphecy->reveal());

        static::assertInstanceOf(GpsTransport::class, $transport);
        static::assertEquals(__DIR__ . '/../Resources/credentials.json', getenv(GpsTransportFactory::GOOGLE_APPLICATION_CREDENTIALS));
        static::assertEquals('random', getenv(GpsTransportFactory::GOOGLE_CLOUD_PROJECT));
        static::assertEquals('address://emulator/host', getenv(GpsTransportFactory::PUBSUB_EMULATOR_HOST));
    }
}
