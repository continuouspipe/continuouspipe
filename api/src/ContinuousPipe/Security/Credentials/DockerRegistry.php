<?php

namespace ContinuousPipe\Security\Credentials;

class DockerRegistry
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var string|null
     */
    private $serverAddress;

    /**
     * @var string|null
     */
    private $fullAddress;

    /**
     * Key-value attributes liked to the Docker Registry.
     *
     * @var array
     */
    private $attributes;

    /**
     * @param string $username
     * @param string $password
     * @param string|null $email
     * @param string|null $serverAddress
     * @param string|null $fullAddress
     * @param array $attributes
     */
    public function __construct($username = null, $password = null, $email = null, $serverAddress = null, string $fullAddress = null, array $attributes = [])
    {
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->serverAddress = $serverAddress;
        $this->fullAddress = $fullAddress;
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getServerAddress()
    {
        if (null === $this->serverAddress && null !== $this->fullAddress) {
            if ($firstSlash = strpos($this->fullAddress, '/')) {
                return substr($this->fullAddress, 0, $firstSlash);
            }
        }

        return $this->serverAddress;
    }

    /**
     * @return null|string
     */
    public function getFullAddress()
    {
        return $this->fullAddress;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes ?: [];
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Magic setter that allows JMS serializer to set values.
     *
     * @param string $attribute
     * @param mixed  $value
     */
    public function __set($attribute, $value)
    {
        $this->$attribute = $value;
    }

    /**
     * @param null|string $serverAddress
     */
    public function setServerAddress(string $serverAddress = null)
    {
        $this->serverAddress = $serverAddress;
    }

    public function equals(DockerRegistry $registry)
    {
        return ($this->fullAddress !== null && $this->fullAddress == $registry->getFullAddress())
            || $this->getServerAddress() == $registry->getServerAddress()
        ;
    }
}
