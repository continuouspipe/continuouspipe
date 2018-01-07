<?php

namespace ContinuousPipe\Managed\ClusterCreation\Permissions;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Billing\Plan\Plan;
use ContinuousPipe\Managed\ClusterCreation\ClusterCreationException;
use ContinuousPipe\Managed\ClusterCreation\ClusterCreationUserException;
use ContinuousPipe\Managed\ClusterCreation\ClusterCreator;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;

class EnsureTeamHasSufficientBilling implements ClusterCreator
{
    /**
     * @var ClusterCreator
     */
    private $decoratedCreator;
    /**
     * @var UserBillingProfileRepository
     */
    private $billingProfileRepository;

    /**
     * @param ClusterCreator $decoratedCreator
     * @param UserBillingProfileRepository $billingProfileRepository
     */
    public function __construct(ClusterCreator $decoratedCreator, UserBillingProfileRepository $billingProfileRepository)
    {
        $this->decoratedCreator = $decoratedCreator;
        $this->billingProfileRepository = $billingProfileRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createForTeam(Team $team, string $clusterIdentifier): Cluster
    {
        try {
            $billingProfile = $this->billingProfileRepository->findByTeam($team);
        } catch (UserBillingProfileNotFound $e) {
            throw new ClusterCreationUserException('Your team is not linked to any billing profile', $e->getCode(), $e);
        }

        if (null === ($plan = $billingProfile->getPlan())) {
            throw new ClusterCreationUserException('Your project billing profile do not have any plan, please chose one.');
        }

        if (!$this->allowManagedCluster($plan)) {
            throw new ClusterCreationUserException('You need to have an active managed plan. Update your project billing profile.');
        }

        return $this->decoratedCreator->createForTeam($team, $clusterIdentifier);
    }

    private function allowManagedCluster(Plan $plan) : bool
    {
        return $plan->getMetrics()->getMemory() > 0;
    }
}
