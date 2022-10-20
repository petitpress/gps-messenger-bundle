<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle;

use PetitPress\GpsMessengerBundle\DependencyInjection\PetitPressGpsMessengerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class GpsMessengerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->registerExtension(new PetitPressGpsMessengerExtension());
    }
}
