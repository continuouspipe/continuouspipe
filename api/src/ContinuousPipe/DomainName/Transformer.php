<?php

namespace ContinuousPipe\DomainName;

use Cocur\Slugify\Slugify;

class Transformer
{
    const HOST_HASH_LENGTH = 11;

    public function shortenWithHash(string $hostPrefix, int $maxLength)
    {
        if (mb_strlen($hostPrefix) <= $maxLength) {
            return $hostPrefix;
        }

        $shortenToLength = $maxLength - self::HOST_HASH_LENGTH;
        $shortenedPrefix = rtrim(mb_substr($hostPrefix, 0, $shortenToLength), '-');
        $hash = mb_substr(md5($hostPrefix), 0, self::HOST_HASH_LENGTH - 1);

        return $shortenedPrefix . '-' . $hash;
    }

    public function slugify(string $hostname)
    {
        return (new Slugify())->slugify($hostname);
    }
}
