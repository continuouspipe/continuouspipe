<?php

namespace ContinuousPipe\Authenticator\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class SystemUser implements UserInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @param string $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return ['ROLE_SYSTEM'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
}
