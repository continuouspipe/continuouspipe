<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Model\Component\Endpoint\CloudFlareAuthentication;

interface CloudFlareClient
{
    /**
     * @param string                   $zone
     * @param CloudFlareAuthentication $authentication
     * @param ZoneRecord               $record
     *
     * @throws CloudFlareException
     */
    public function createRecord($zone, CloudFlareAuthentication $authentication, ZoneRecord $record);
}
