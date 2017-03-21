<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\DynamicVariable;

use Cocur\Slugify\Slugify;
use ContinuousPipe\Pipe\Client\PublicEndpoint;

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
     * Create new instance from a public endpoint object
     *
     * @param PublicEndpoint $endpoint
     *
     * @return ServiceVariable
     */
    public static function fromPublicEndpoint(PublicEndpoint $endpoint)
    {
        $instance = new self();
        $instance->name = $endpoint->getName();
        $instance->address = $endpoint->getAddress();
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
        return preg_match('/^SERVICE_[^a-z]+_PUBLIC_ENDPOINT$/', $name) === 1;
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        $name = mb_strtoupper((new Slugify(['regex' => '/([^A-Za-z0-9])+/']))->slugify($this->name, '_'));
        return sprintf('SERVICE_%s_PUBLIC_ENDPOINT', $name);
    }

    /**
     * @return string|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    private function __construct()
    {
    }
}
