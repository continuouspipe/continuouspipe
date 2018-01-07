<?php

namespace ContinuousPipe\Model\Extension\ReverseProxy;

use ContinuousPipe\Model\Extension;

class ReverseProxyExtension implements Extension
{
    const NAME = 'reverse_proxy';

    /**
     * @var string[]
     */
    private $domainNames;

    /**
     * @return string[]
     */
    public function getDomainNames()
    {
        return $this->domainNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
