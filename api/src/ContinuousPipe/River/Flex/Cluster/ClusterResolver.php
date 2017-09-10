<?php

namespace ContinuousPipe\River\Flex\Cluster;

use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;

class ClusterResolver
{
    /**
     * @var string
     */
    private $flexClusterUrl;

    /**
     * @param string $flexClusterUrl
     */
    public function __construct(string $flexClusterUrl)
    {
        $this->flexClusterUrl = $flexClusterUrl;
    }

    public function getCluster() : Cluster
    {
        if (false === ($clusterUrlComponents = parse_url($this->flexClusterUrl))) {
            throw new \RuntimeException('Unable to get cluster\'s details from the URL');
        }

        return new Cluster\Kubernetes(
            'flex',
            $clusterUrlComponents['scheme'].'://'.$clusterUrlComponents['host'],
            'v1.6',
            $clusterUrlComponents['user'],
            $clusterUrlComponents['pass']
        );
    }
}
