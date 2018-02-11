<?php

namespace ContinuousPipe\Managed\ClusterCreation;

use ContinuousPipe\River\CodeRepository\FileSystem\LocalRelativeFileSystem;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;

class KubernetesInceptionCreator implements ClusterCreator
{
    /**
     * @var RelativeFileSystem
     */
    private $fileSystem;

    public function __construct(RelativeFileSystem $fileSystem = null)
    {
        $this->fileSystem = $fileSystem ?: new LocalRelativeFileSystem();
    }

    /**
     * {@inheritdoc}
     */
    public function createForTeam(Team $team, string $clusterIdentifier, string $dsn): Cluster
    {
        if (!$this->fileSystem->exists($tokenPath = '/var/run/secrets/kubernetes.io/serviceaccount/token')) {
            throw new ClusterCreationException('Token file required for inception was not found.');
        }

        $cluster = new Cluster\Kubernetes(
            $clusterIdentifier,
            sprintf('https://%s', getenv('KUBERNETES_SERVICE_HOST')),
            'v1.7'
        );

        $cluster->setCredentials(new Cluster\ClusterCredentials(
            null, null, null, null, null,
            $this->fileSystem->getContents($tokenPath)
        ));

        return $cluster;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Team $team, string $clusterIdentifier, string $dsn): bool
    {
        return ($parsedDsn = parse_url($dsn)) && $parsedDsn['scheme'] == 'kinception';
    }
}
