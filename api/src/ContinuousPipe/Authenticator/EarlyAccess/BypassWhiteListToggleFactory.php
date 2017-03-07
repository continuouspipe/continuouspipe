<?php

namespace ContinuousPipe\Authenticator\EarlyAccess;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class BypassWhiteListToggleFactory
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
        return new BypassWhiteListToggle($attributeBag);
    }
}
