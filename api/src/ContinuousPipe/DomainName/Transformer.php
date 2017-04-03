<?php

namespace ContinuousPipe\DomainName;

use Cocur\Slugify\Slugify;

class Transformer
{
    const HOST_HASH_LENGTH = 10;

    public function shortenWithHash(string $hostPrefix, int $maxLength)
    {
        if (mb_strlen($hostPrefix) <= $maxLength) {
            return $hostPrefix;
        }

        $shortenToLength = ($maxLength - self::HOST_HASH_LENGTH) - 1;
        $shortenedPrefix = mb_substr($hostPrefix, 0, $shortenToLength);
        $hash = mb_substr(md5($hostPrefix), 0, self::HOST_HASH_LENGTH);

        return $shortenedPrefix . '-' . $hash;
    }

    public function slugify(string $hostname)
    {
        return (new Slugify(['regexp' => '/([^A-Za-z0-9\.]|-)+/']))->slugify($hostname);
    }
}