<?php

namespace ContinuousPipe\HttpLabs\Encryption;

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

    public static function from(string $stackId)
    {
        return new self($stackId);
    }

    public function __toString()
    {
        return $this->namespace;
    }
}
