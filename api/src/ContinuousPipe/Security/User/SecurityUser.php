<?php

namespace ContinuousPipe\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class SecurityUser implements UserInterface
{
    /**
     * @var User
     */
    private $user;

    private $username;
    private $salt;
    private $password;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->username = $user->getUsername();
        $this->user = $user;
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
    public function getRoles()
    {
        $roles = $this->user->getRoles();

        if (empty($roles)) {
            $roles = ['ROLE_USER'];
        }

        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
