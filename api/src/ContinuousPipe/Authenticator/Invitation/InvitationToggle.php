<?php

namespace ContinuousPipe\Authenticator\Invitation;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class InvitationToggle
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
        return true === $this->storage->get('authenticator.invitation', false);
    }

    public function activate()
    {
        $this->storage->set('authenticator.invitation', true);
    }
}
