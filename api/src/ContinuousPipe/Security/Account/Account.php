<?php

namespace ContinuousPipe\Security\Account;

abstract class Account
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var null|string
     */
    private $email;

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var null|string
     */
    private $pictureUrl;

    /**
     * @param string      $uuid
     * @param string      $username
     * @param string      $identifier
     * @param string|null $email
     * @param string|null $name
     * @param string|null $pictureUrl
     */
    public function __construct(string $uuid, string $username, string $identifier, string $email = null, string $name = null, string $pictureUrl = null)
    {
        $this->uuid = $uuid;
        $this->username = $username;
        $this->identifier = $identifier;
        $this->email = $email;
        $this->name = $name;
        $this->pictureUrl = $pictureUrl;
    }

    /**
     * @return string
     */
    public function getUuid() : string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getPictureUrl()
    {
        return $this->pictureUrl;
    }

    /**
     * @return string
     */
    abstract public function getType() : string;
}
