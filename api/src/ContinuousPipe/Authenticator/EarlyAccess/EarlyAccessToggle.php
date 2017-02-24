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

    public function activate(EarlyAccessCode $earlyAccessCode)
    {
        $this->storage->set('authenticator.early_access', true);
        $this->storage->set('authenticator.early_access_code', $earlyAccessCode);
    }

    public function getUsedEarlyAccessCode(): EarlyAccessCode
    {
        if (!$this->isActive()) {
            throw new \LogicException('Early access has to be activated first.');
        }

        if (null === ($earlyAccessCode = $this->storage->get('authenticator.early_access_code'))) {
            throw new \RuntimeException('Early access code can not be found.');
        }

        return $earlyAccessCode;
    }
}
