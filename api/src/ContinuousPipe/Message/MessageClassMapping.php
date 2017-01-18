<?php

namespace ContinuousPipe\Message;

final class MessageClassMapping
{
    private static $mapping = [
        'user_activity' => UserActivity::class,
    ];

    public static function fromName(string $name) : string
    {
        if (!array_key_exists($name, self::$mapping)) {
            throw new \InvalidArgumentException(sprintf('No name mapping found for the message named "%s"', $name));
        }

        return self::$mapping[$name];
    }

    public static function toName(Message $message) : string
    {
        if (false === ($name = array_search(get_class($message), self::$mapping))) {
            throw new \InvalidArgumentException(sprintf('No name mapping found for the message "%s"', get_class($message)));
        }

        return $name;
    }
}
