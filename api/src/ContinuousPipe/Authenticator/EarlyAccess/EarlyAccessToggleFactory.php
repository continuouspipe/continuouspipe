<?php

namespace ContinuousPipe\Authenticator\EarlyAccess;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EarlyAccessToggleFactory
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
        return new EarlyAccessToggle($attributeBag);
    }
}
