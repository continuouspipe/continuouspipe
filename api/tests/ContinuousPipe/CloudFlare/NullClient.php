<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Model\Component\Endpoint\CloudFlareAuthentication;

class NullClient implements CloudFlareClient
{
    /**
     * {@inheritdoc}
     */
    public function createRecord($zone, CloudFlareAuthentication $authentication, ZoneRecord $record)
    {
    }
}
