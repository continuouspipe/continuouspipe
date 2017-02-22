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
     * @var \ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessToggle
     */
    private $earlyAccessToggle;

    public function __construct(WhiteList $decoratedWhiteList, EarlyAccessToggle $earlyAccessToggle)
    {
        $this->earlyAccessToggle = $earlyAccessToggle;
        $this->decoratedWhiteList = $decoratedWhiteList;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($username)
    {
        if ($this->earlyAccessToggle->isActive()) {
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
