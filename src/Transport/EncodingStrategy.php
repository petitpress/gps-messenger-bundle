<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

enum EncodingStrategy: string
{
    case Wrapped = 'wrapped';
    case Hybrid = 'hybrid';
    case Flat = 'flat';
}
