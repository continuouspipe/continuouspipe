<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\DynamicVariable;

/**
 * This value object represents a dynamic endpoint variable
 *
 * It holds the public DNS address of the endpoint.
 * This value is originated from the Pipe component.
 */
final class EndpointVariable
{
    /**
     * Tells whether the given variable name is a valid identifier
     *
     * @param string $name Variable name.
     *
     * @return boolean
     */
    public static function isValidVariableName($name): bool
    {
        return preg_match('/^ENDPOINT_[^a-z]+_PUBLIC_ENDPOINT$/', $name) === 1;
    }

    private function __construct()
    {
    }
}