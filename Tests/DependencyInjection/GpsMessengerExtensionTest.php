<?php

declare(strict_types=1);

namespace DependencyInjection;

use PetitPress\GpsMessengerBundle\DependencyInjection\GpsMessengerExtension;
use PetitPress\GpsMessengerBundle\Transport\GpsTransportFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Parser;

/**
 * @group legacy
 */
class GpsMessengerExtensionTest extends TestCase
{
    private ?ContainerBuilder $configuration;

    protected function tearDown(): void
    {
        $this->configuration = null;
    }

    public function testSimpleConfiguration(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new GpsMessengerExtension();
        $config = $this->getSimpleConfig();
        $loader->load([$config], $this->configuration);

        $this->assertTrue($this->configuration->hasDefinition(GpsTransportFactory::class));
        $gpsTransportFactoryDefinition = $this->configuration->getDefinition(GpsTransportFactory::class);
        $cacheArgument = $gpsTransportFactoryDefinition->getArgument(1);
        $this->assertInstanceOf(Reference::class, $cacheArgument);
        $this->assertEquals('cache.app', (string) $cacheArgument);
    }

    /**
     * @return mixed
     */
    private function getSimpleConfig()
    {
        // use all defaults
        return (new Parser())->parse('');
    }

    public function testFullConfiguration(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new GpsMessengerExtension();
        $config = $this->getFullConfig();
        $loader->load([$config], $this->configuration);

        $this->assertTrue($this->configuration->hasDefinition(GpsTransportFactory::class));
        $gpsTransportFactoryDefinition = $this->configuration->getDefinition(GpsTransportFactory::class);
        $cacheArgument = $gpsTransportFactoryDefinition->getArgument(1);
        $this->assertInstanceOf(Reference::class, $cacheArgument);
        $this->assertEquals('foo', (string) $cacheArgument);
    }

    /**
     * @return mixed
     */
    private function getFullConfig()
    {
        $yaml = <<<EOF
auth_cache: 'foo'
EOF;

        return (new Parser())->parse($yaml);
    }


    public function testConfigurationWithDisabledAuthCache(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new GpsMessengerExtension();
        $config = $this->getDisabledCacheConfig();
        $loader->load([$config], $this->configuration);

        $this->assertTrue($this->configuration->hasDefinition(GpsTransportFactory::class));
        $gpsTransportFactoryDefinition = $this->configuration->getDefinition(GpsTransportFactory::class);
        $this->assertNull($gpsTransportFactoryDefinition->getArgument(1));
    }

    /**
     * @return mixed
     */
    private function getDisabledCacheConfig()
    {
        $yaml = <<<EOF
auth_cache: false
EOF;

        return (new Parser())->parse($yaml);
    }
}
