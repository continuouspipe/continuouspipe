<?php

namespace ContinuousPipe\Authenticator\EarlyAccess;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class EarlyAccessToggle
{
    /**
     * @var AttributeBagInterface
     */
    private $storage;

    public function __construct(AttributeBagInterface $storage)
    {
        $this->storage = $storage;
    }

    public function isActive()
    {
        return true === $this->storage->get('authenticator.early_access', false);
    }

    public function activate()
    {
        $this->storage->set('authenticator.early_access', true);
    }
}
