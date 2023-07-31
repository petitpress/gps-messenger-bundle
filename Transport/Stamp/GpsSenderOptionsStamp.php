<?php

namespace PetitPress\GpsMessengerBundle\Transport\Stamp;

/**
 * @author Damien AVETTA-RAYMOND <damien.avetta@gmail.com>
 */
class GpsSenderOptionsStamp
{
    /**
     * @var array<string, object>
     */
    private array $options;

    /**
     * @param array<string, object> $options
     */
    public function __construct(array $options) {
        $this->options = $options;
    }

    /**
     * @return array<string, object>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
