<?php

namespace ContinuousPipe\CloudFlare;

use Cloudflare\Zone\Dns;
use ContinuousPipe\Model\Component\Endpoint\CloudFlareAuthentication;

interface AuthenticatedCloudFlareClientFactory
{
    public function dns(CloudFlareAuthentication $authentication) : Dns;
}
