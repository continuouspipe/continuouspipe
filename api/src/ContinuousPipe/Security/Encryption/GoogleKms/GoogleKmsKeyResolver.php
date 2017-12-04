<?php

namespace ContinuousPipe\Security\Encryption\GoogleKms;

use ContinuousPipe\Security\Encryption\EncryptionException;

interface GoogleKmsKeyResolver
{
    /**
     * @param string $namespace
     *
     * @throws EncryptionException
     *
     * @return string
     */
    public function keyName(string $namespace) : string;
}
