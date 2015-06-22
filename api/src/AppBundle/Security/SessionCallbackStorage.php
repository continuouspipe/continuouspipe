<?php

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SessionCallbackStorage implements CallbackStorage
{
    const BAG_STORAGE_KEY = 'authentication';
    const ATTRIBUTE_CALLBACK = 'callback';

    /**
     * @var SessionStorageInterface
     */
    private $sessionStorage;

    public function __construct(SessionStorageInterface $sessionStorage)
    {
        $this->sessionStorage = $sessionStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(UserInterface $user)
    {
        return $this->getBag()->get(self::ATTRIBUTE_CALLBACK);
    }

    /**
     * {@inheritdoc}
     */
    public function saveByUser(UserInterface $user, $callback)
    {
        $this->getBag()->set(self::ATTRIBUTE_CALLBACK, $callback);
    }

    /**
     * @return AttributeBag
     */
    private function getBag()
    {
        try {
            $bag = $this->sessionStorage->getBag(self::BAG_STORAGE_KEY);
        } catch (\InvalidArgumentException $e) {
            $bag = new AttributeBag(self::BAG_STORAGE_KEY);
            $this->sessionStorage->registerBag($bag);
        }

        return $bag;
    }
}
