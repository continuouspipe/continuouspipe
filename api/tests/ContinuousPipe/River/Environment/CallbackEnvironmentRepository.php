<?php

namespace ContinuousPipe\River\Environment;

use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;

class CallbackEnvironmentRepository implements DeployedEnvironmentRepository
{
    /**
     * @var DeployedEnvironmentRepository
     */
    private $decoratedRepository;

    /**
     * @var callable|null
     */
    private $deleteEnvironmentCallback;

    /**
     * @param DeployedEnvironmentRepository $decoratedRepository
     */
    public function __construct(DeployedEnvironmentRepository $decoratedRepository)
    {
        $this->decoratedRepository = $decoratedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlow(FlatFlow $flow)
    {
        return $this->decoratedRepository->findByFlow($flow);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Team $team, User $user, DeployedEnvironment $environment)
    {
        if (null !== ($callback = $this->deleteEnvironmentCallback)) {
            return $callback($team, $user, $environment);
        }

        return $this->decoratedRepository->delete($team, $user, $environment);
    }

    /**
     * {@inheritdoc}
     */
    public function deletePod(FlatFlow $flow, string $clusterIdentifier, string $namespace, string $podName)
    {
        return $this->decoratedRepository->deletePod($flow, $clusterIdentifier, $namespace, $podName);
    }

    /**
     * @param callable|null $deleteEnvironmentCallback
     */
    public function setDeleteEnvironmentCallback($deleteEnvironmentCallback)
    {
        $this->deleteEnvironmentCallback = $deleteEnvironmentCallback;
    }
}
