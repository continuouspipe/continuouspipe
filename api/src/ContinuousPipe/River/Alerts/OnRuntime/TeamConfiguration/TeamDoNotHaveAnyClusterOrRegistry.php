<?php

namespace ContinuousPipe\River\Alerts\OnRuntime\TeamConfiguration;

use ContinuousPipe\River\Alerts\Alert;
use ContinuousPipe\River\Alerts\AlertAction;
use ContinuousPipe\River\Alerts\AlertsRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\Security\Credentials\BucketRepository;

class TeamDoNotHaveAnyClusterOrRegistry implements AlertsRepository
{
    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param BucketRepository $bucketRepository
     */
    public function __construct(BucketRepository $bucketRepository)
    {
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlow(FlatFlow $flow)
    {
        $bucket = $this->bucketRepository->find(
            $flow->getTeam()->getBucketUuid()
        );

        $alerts = [];

        if ($bucket->getClusters()->isEmpty()) {
            $alerts[] = new Alert(
                'team-missing-cluster',
                'The team does not have a configured cluster',
                new \DateTime(),
                new AlertAction(
                    'state',
                    'Configure',
                    'clusters'
                )
            );
        }

        if ($bucket->getDockerRegistries()->isEmpty()) {
            $alerts[] = new Alert(
                'team-missing-docker-registry',
                'The team does not have a registry to push the Docker images to',
                new \DateTime(),
                new AlertAction(
                    'state',
                    'Configure',
                    'registry-credentials'
                )
            );
        }

        return $alerts;
    }
}
