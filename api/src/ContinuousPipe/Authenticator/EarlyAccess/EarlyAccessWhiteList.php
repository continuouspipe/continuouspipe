<?php

namespace ContinuousPipe\Authenticator\EarlyAccess;

use ContinuousPipe\Authenticator\WhiteList\WhiteList;

/**
 * Whitelist users who are provided valid early access code
 */
class EarlyAccessWhiteList implements WhiteList
{
    /**
     * @var WhiteList
     */
    private $decoratedWhiteList;

    /**
     * @var \ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessToggleFactory
     */
    private $earlyAccessToggleFactory;

    public function __construct(WhiteList $decoratedWhiteList, EarlyAccessToggleFactory $earlyAccessToggleFactory)
    {
        $this->earlyAccessToggleFactory = $earlyAccessToggleFactory;
        $this->decoratedWhiteList = $decoratedWhiteList;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($username)
    {
        $earlyAccessToggle = $this->earlyAccessToggleFactory->createFromSession();
        if ($earlyAccessToggle->isActive()) {
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
