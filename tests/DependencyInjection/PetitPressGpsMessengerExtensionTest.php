<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\DependencyInjection;

use PetitPress\GpsMessengerBundle\DependencyInjection\PetitPressGpsMessengerExtension;
use PetitPress\GpsMessengerBundle\Transport\EncodingStrategy;
use PetitPress\GpsMessengerBundle\Transport\GpsTransportFactory;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Parser;

class PetitPressGpsMessengerExtensionTest extends TestCase
{
    #[IgnoreDeprecations]
    public function testSimpleConfiguration(): void
    {
        $configuration = new ContainerBuilder();
        $loader = new PetitPressGpsMessengerExtension();
        $config = $this->getSimpleConfig();
        $loader->load([$config], $configuration);

        static::assertTrue($configuration->hasDefinition(GpsTransportFactory::class));
        $gpsTransportFactoryDefinition = $configuration->getDefinition(GpsTransportFactory::class);
        $cacheArgument = $gpsTransportFactoryDefinition->getArgument(1);
        static::assertInstanceOf(Reference::class, $cacheArgument);
        static::assertEquals('cache.app', (string) $cacheArgument);
        static::assertEquals(EncodingStrategy::Wrapped, $gpsTransportFactoryDefinition->getArgument(3));
    }

    /**
     * @return array<string, mixed>
     */
    private function getSimpleConfig(): array
    {
        // use all defaults — empty YAML parses to null, so return [] explicitly
        return [];
    }

    public function testFullConfiguration(): void
    {
        $configuration = new ContainerBuilder();
        $loader = new PetitPressGpsMessengerExtension();
        $config = $this->getFullConfig();
        $loader->load([$config], $configuration);

        static::assertTrue($configuration->hasDefinition(GpsTransportFactory::class));
        $gpsTransportFactoryDefinition = $configuration->getDefinition(GpsTransportFactory::class);
        $cacheArgument = $gpsTransportFactoryDefinition->getArgument(1);
        static::assertInstanceOf(Reference::class, $cacheArgument);
        static::assertEquals('foo', (string) $cacheArgument);
        static::assertEquals(EncodingStrategy::Flat, $gpsTransportFactoryDefinition->getArgument(3));
    }

    /**
     * @return array<string, mixed>
     */
    private function getFullConfig(): array
    {
        $yaml = <<<EOF
auth_cache: 'foo'
encoding_strategy: 'flat'
EOF;
        /** @var array<string, mixed> */
        return (new Parser())->parse($yaml);
    }


    public function testConfigurationWithDisabledAuthCache(): void
    {
        $configuration = new ContainerBuilder();
        $loader = new PetitPressGpsMessengerExtension();
        $config = $this->getDisabledCacheConfig();
        $loader->load([$config], $configuration);

        static::assertTrue($configuration->hasDefinition(GpsTransportFactory::class));
        $gpsTransportFactoryDefinition = $configuration->getDefinition(GpsTransportFactory::class);
        static::assertNull($gpsTransportFactoryDefinition->getArgument(1));
    }

    /**
     * @return array<string, mixed>
     */
    private function getDisabledCacheConfig(): array
    {
        $yaml = <<<EOF
auth_cache: false
EOF;
        /** @var array<string, mixed> */
        return (new Parser())->parse($yaml);
    }
}
