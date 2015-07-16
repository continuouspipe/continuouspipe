<?php

namespace ContinuousPipe\Builder;

class RegistryCredentials
{
    private $authenticationString;

    public static function fromAuthenticationString($authenticationString)
    {
        $credentials = new self();
        $credentials->authenticationString = $authenticationString;

        return $credentials;
    }

    /**
     * @return string
     */
    public function getAuthenticationString()
    {
        return $this->authenticationString;
    }
}