<?php

namespace ContinuousPipe\Managed\ClusterCreation;

class Hashifier
{
    public static function hash(string $string, int $length) : string
    {
        return substr(md5($string), 0, $length);
    }

    public static function maxLength(string $string, int $maxLength, int $hashLength = 5)
    {
        if (strlen($string) <= $maxLength) {
            return $string;
        }

        $partOfTheStringToBeCut = substr($string, $maxLength - $hashLength);

        return substr($string, 0, $maxLength - $hashLength).self::hash($partOfTheStringToBeCut, $hashLength);
    }
}
