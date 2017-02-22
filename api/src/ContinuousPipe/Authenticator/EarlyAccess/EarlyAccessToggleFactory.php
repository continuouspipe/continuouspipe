<?php

namespace ContinuousPipe\Authenticator\EarlyAccess;

use Symfony\Component\HttpFoundation\Session\Session;

class EarlyAccessToggleFactory
{
    public static function createWithSessionStorage(Session $session)
    {
        $attributeBag = $session->getBag('attributes');
        return new EarlyAccessToggle($attributeBag);
    }
}
