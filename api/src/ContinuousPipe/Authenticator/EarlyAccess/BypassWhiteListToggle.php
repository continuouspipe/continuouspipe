<?php

namespace ContinuousPipe\Authenticator\EarlyAccess;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class BypassWhiteListToggle
{
    /**
     * @var AttributeBagInterface
     */
    private $storage;

    public function __construct(AttributeBagInterface $storage)
    {
        $this->storage = $storage;
    }

    public function isActive(): bool
    {
        return true === $this->storage->get('authenticator.bypass_white_list', false);
    }

    public function activate()
    {
        $this->storage->set('authenticator.bypass_white_list', true);
    }

    public function activateByCode(EarlyAccessCode $earlyAccessCode)
    {
        $this->activate();

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
