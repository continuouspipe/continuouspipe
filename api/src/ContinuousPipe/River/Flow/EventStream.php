<?php

namespace ContinuousPipe\River\Flow;

use Ramsey\Uuid\UuidInterface;

final class EventStream
{
    private $name;

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function fromUuid(UuidInterface $uuid) : EventStream
    {
        return new self(
            'Flow-'.$uuid->toString()
        );
    }

    public function __toString()
    {
        return $this->name;
    }
}
