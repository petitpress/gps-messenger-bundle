<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport\Stamp;

use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

/**
 * @author Jules Pietri <jules@heahprod.com>
 */
final class AttributesStamp implements NonSendableStampInterface
{
    /**
     * @var array<string, string>
     */
    private array $attributes;

    /**
     * @param array<string, string> $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array<string, string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
