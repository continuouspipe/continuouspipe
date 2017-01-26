<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\DynamicVariable;

/**
 * This value object represents a dynamic service variable
 *
 * It holds the network address of the given service.
 */
final class ServiceVariable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $address;

    /**
     * Create new instance from service name and address
     *
     * @param string $name Service name.
     * @param string|null $address Service network address.
     *
     * @return ServiceVariable
     */
    public static function fromNameAndAddress($name, $address)
    {
        $instance = new self();
        $instance->name = $name;
        $instance->address = $address;
        return $instance;
    }

    /**
     * Tells whether the given variable name is a valid identifier
     *
     * @param string $name Variable name.
     *
     * @return boolean
     */
    public static function isValidVariableName($name): bool
    {
        return preg_match('/^SERVICE_[^_a-z]+_PUBLIC_ENDPOINT$/', $name) === 1;
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        $name = str_replace('_', '', mb_strtoupper($this->name));
        return sprintf('SERVICE_%s_PUBLIC_ENDPOINT', $name);
    }

    /**
     * @return string|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    public function __toString()
    {
        return (string)$this->getAddress();
    }

    private function __construct()
    {
    }
}