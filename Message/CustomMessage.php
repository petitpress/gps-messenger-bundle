<?php declare(strict_types = 1);

namespace PetitPress\GpsMessengerBundle\Message;

class CustomMessage
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
