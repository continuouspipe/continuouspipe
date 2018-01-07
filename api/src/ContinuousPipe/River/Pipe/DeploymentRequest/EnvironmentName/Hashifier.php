<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName;

class Hashifier
{
    public static function hash(string $string, int $length) : string
    {
        return substr(md5($string), 0, $length);
    }
}
