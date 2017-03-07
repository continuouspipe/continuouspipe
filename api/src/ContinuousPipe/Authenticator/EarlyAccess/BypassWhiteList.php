<?php

namespace ContinuousPipe\Authenticator\EarlyAccess;

use ContinuousPipe\Authenticator\WhiteList\WhiteList;

class BypassWhiteList implements WhiteList
{
    /**
     * @var WhiteList
     */
    private $decoratedWhiteList;

    /**
     * @var \ContinuousPipe\Authenticator\EarlyAccess\BypassWhiteListToggleFactory
     */
    private $bypassWhiteListToggleFactory;

    public function __construct(
        WhiteList $decoratedWhiteList,
        BypassWhiteListToggleFactory $bypassWhiteListToggleFactory
    ) {
        $this->bypassWhiteListToggleFactory = $bypassWhiteListToggleFactory;
        $this->decoratedWhiteList = $decoratedWhiteList;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($username)
    {
        $bypassWhiteListToggle = $this->bypassWhiteListToggleFactory->createFromSession();
        if ($bypassWhiteListToggle->isActive()) {
            return true;
        }

        return $this->decoratedWhiteList->contains($username);
    }


    /**
     * {@inheritdoc}
     */
    public function add($username)
    {
        $this->decoratedWhiteList->add($username);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($username)
    {
        $this->decoratedWhiteList->remove($username);
    }
}
