<?php

namespace ContinuousPipe\CloudFlare;

use Cloudflare\Zone\Dns;
use ContinuousPipe\Model\Component\Endpoint\CloudFlareAuthentication;

class OfficialClientCloudFlareClientFactory implements AuthenticatedCloudFlareClientFactory
{
    /**
     * {@inheritdoc}
     */
    public function dns(CloudFlareAuthentication $authentication): Dns
    {
        return new Dns($authentication->getEmail(), $authentication->getApiKey());
    }
}
