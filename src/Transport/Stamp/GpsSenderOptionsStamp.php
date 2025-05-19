<?php

namespace PetitPress\GpsMessengerBundle\Transport\Stamp;

use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

/**
 * @author Damien AVETTA-RAYMOND <damien.avetta@gmail.com>
 */
class GpsSenderOptionsStamp implements NonSendableStampInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options) {
        $this->options = $options;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
