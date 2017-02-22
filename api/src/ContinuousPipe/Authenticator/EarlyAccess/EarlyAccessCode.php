<?php

namespace ContinuousPipe\Authenticator\EarlyAccess;

/**
 * This value object represents the code used for activating the early access program.
 */
class EarlyAccessCode
{
    private $code;

    private function __construct()
    {
    }

    public static function fromString(string $code): EarlyAccessCode
    {
        $instance = new self;
        $instance->code = $code;
        return $instance;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function __toString()
    {
        return $this->code;
    }
}
