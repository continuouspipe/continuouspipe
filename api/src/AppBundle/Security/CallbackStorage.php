<?php

namespace AppBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

interface CallbackStorage
{
    /**
     * @param UserInterface $user
     * @return string
     */
    public function findByUser(UserInterface $user);

    /**
     * @param UserInterface $user
     * @param string $callback
     */
    public function saveByUser(UserInterface $user, $callback);
}
