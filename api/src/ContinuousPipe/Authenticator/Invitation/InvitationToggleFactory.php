<?php

namespace ContinuousPipe\Authenticator\Invitation;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class InvitationToggleFactory
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function createFromSession()
    {
        /** @var AttributeBagInterface $attributeBag */
        $attributeBag = $this->session->getBag('attributes');
        return new InvitationToggle($attributeBag);
    }
}
