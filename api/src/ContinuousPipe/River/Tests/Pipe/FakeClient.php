<?php

namespace ContinuousPipe\River\Tests\Pipe;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;

class FakeClient implements Client
{
    /**
     * @var Environment[]
     */
    private $environments = [];

    /**
     * {@inheritdoc}
     */
    public function start(DeploymentRequest $deploymentRequest, User $user)
    {
        return new Client\Deployment(
            Uuid::uuid1(),
            $deploymentRequest,
            Client\Deployment::STATUS_PENDING
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEnvironment(DeploymentRequest\Target $target, Team $team, User $authenticatedUser)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironments($clusterIdentifier, Team $team, User $authenticatedUser)
    {
        return $this->environments;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentsLabelled($clusterIdentifier, Team $team, User $authenticatedUser, array $labels)
    {
        return array_values(array_filter($this->environments, function (Environment $environment) use ($labels) {
            $environmentLabels = $environment->getLabels();

            foreach ($labels as $key => $value) {
                if (!array_key_exists($key, $environmentLabels)) {
                    return false;
                } elseif ($environmentLabels[$key] != $value) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * @param Environment $environment
     */
    public function addEnvironment(Environment $environment)
    {
        $this->environments[] = $environment;
    }
}
