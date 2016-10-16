<?php

namespace ContinuousPipe\Pipe\Uuid;

use Ramsey\Uuid\Uuid;

class UuidTransformer
{
    /**
     * @param mixed $uuid
     *
     * @return Uuid
     */
    public static function transform($uuid)
    {
        if (null === $uuid || $uuid instanceof Uuid) {
            return $uuid;
        }

        return Uuid::fromString((string) $uuid);
    }
}
