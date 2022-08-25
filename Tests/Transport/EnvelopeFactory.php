<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @author Mickael Prévôt <mickael.prevot@ext.adeo.com>
 */
class EnvelopeFactory
{
    public static function create(?StampInterface ...$stamps): Envelope
    {
        return new Envelope(new \stdClass(), $stamps ?: []);
    }
}
