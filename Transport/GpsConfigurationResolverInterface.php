<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

interface GpsConfigurationResolverInterface
{
    public const DEFAULT_TOPIC_NAME = 'messages';
    public const DEFAULT_MAX_MESSAGES_PULL = 10;
    public const CREATION_ENABLE = true;

    public function resolve(string $dsn, array $options): GpsConfigurationInterface;
}
