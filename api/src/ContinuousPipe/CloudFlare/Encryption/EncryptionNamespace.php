<?php

namespace ContinuousPipe\CloudFlare\Encryption;

final class EncryptionNamespace
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @param string $namespace
     */
    private function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    public static function from(string $zoneIdentifier, string $recordIdentifier)
    {
        return new self(
            $zoneIdentifier.'-'.$recordIdentifier
        );
    }

    public function __toString()
    {
        return $this->namespace;
    }
}
