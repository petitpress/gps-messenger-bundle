<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Stub\DTO;

final readonly class StubDto
{
    public function __construct(
        public string $property1,
        public string $property2,
    ) {
    }
}
