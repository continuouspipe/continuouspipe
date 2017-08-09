<?php

namespace ContinuousPipe\River\Tests\Pipe;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\Promise;
use Ramsey\Uuid\Uuid;

class FakeClient implements Client
{
    /**
     * @var Environment[]
     */
    private $environmentsPerCluster = [];

    /**
     * @var array
     */
    private $pods;

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
        if (!array_key_exists($target->getClusterIdentifier(), $this->environmentsPerCluster)) {
            return;
        }

        foreach ($this->environmentsPerCluster[$target->getClusterIdentifier()] as $key => $environment) {
            if ($environment->getName() == $target->getEnvironmentName()) {
                unset($this->environmentsPerCluster[$target->getClusterIdentifier()][$key]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deletePod(Team $team, User $authenticatedUser, string $clusterIdentifier, string $namespace, string $podName)
    {
        if (
            !isset($this->pods[$team->getSlug()][$clusterIdentifier][$namespace])
            ||
            $this->pods[$team->getSlug()][$clusterIdentifier][$namespace] !== $podName
        ) {
            throw new \Exception(sprintf('Pod %s not found', $podName));
        }

        unset($this->pods[$team->getSlug()][$clusterIdentifier][$namespace]);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironments($clusterIdentifier, Team $team, User $authenticatedUser)
    {
        if (!array_key_exists($clusterIdentifier, $this->environmentsPerCluster)) {
            return Promise\promise_for([]);
        }

        return Promise\promise_for($this->environmentsPerCluster[$clusterIdentifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentsLabelled($clusterIdentifier, Team $team, User $authenticatedUser, array $labels)
    {
        $environments = $this->getEnvironments($clusterIdentifier, $team, $authenticatedUser);

        return $environments->then(function (array $environments) use ($labels) {
            return array_values(array_filter($environments, function (Environment $environment) use ($labels) {
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
        });
    }

    /**
     * @param Environment $environment
     */
    public function addEnvironment($clusterIdentifier, Environment $environment)
    {
        if (!array_key_exists($clusterIdentifier, $this->environmentsPerCluster)) {
            $this->environmentsPerCluster[$clusterIdentifier] = [];
        }

        $this->environmentsPerCluster[$clusterIdentifier][] = $environment;
    }

    public function addPod(Team $team, string $podName, string $clusterIdentifier, string $namespace)
    {
        $this->pods[$team->getSlug()][$clusterIdentifier][$namespace] = $podName;
    }
}
