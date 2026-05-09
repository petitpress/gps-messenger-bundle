<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

enum EncodingStrategy: string
{
    /**
     * Pub/Sub attribute key used to mark messages encoded with the flat strategy.
     * Reserved name: must not be set via AttributesStamp by users.
     */
    public const ENCODING_ATTRIBUTE = 'ppgps-encoding-version';

    /**
     * Current value of the encoding attribute. Bumping this value lets the receiver
     * detect future encoding changes and is the only way the receiver routes a
     * message to the flat decoder.
     */
    public const ENCODING_VERSION = '2';

    case Wrapped = 'wrapped';
    case Hybrid = 'hybrid';
    case Flat = 'flat';
}
