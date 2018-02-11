<?php

namespace spec\ContinuousPipe\Managed\ClusterCreation\Billing;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Managed\ClusterCreation\ClusterCreationUserException;
use ContinuousPipe\Managed\ClusterCreation\ClusterCreator;
use ContinuousPipe\Platform\FeatureFlag\FlagResolver;
use ContinuousPipe\Platform\FeatureFlag\Flags;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use ContinuousPipe\Security\Team\Team;
use PhpSpec\ObjectBehavior;

class EnsureTeamHasSufficientBillingSpec extends ObjectBehavior
{
    function let(ClusterCreator $decorated, UserBillingProfileRepository $userBillingProfileRepository, FlagResolver $flagResolver)
    {
        $flagResolver->isEnabled(Flags::BILLING)->willReturn(true);

        $this->beConstructedWith($decorated, $userBillingProfileRepository, $flagResolver);
    }

    function it_is_a_cluster_creator()
    {
        $this->shouldImplement(ClusterCreator::class);
    }

    function it_refuses_the_creation_if_no_billing_profile_found_for_team(UserBillingProfileRepository $userBillingProfileRepository)
    {
        $team = new Team('slug', 'Name');

        $userBillingProfileRepository->findByTeam($team)->willThrow(new UserBillingProfileNotFound('Profile not found'));

        $this->shouldThrow(ClusterCreationUserException::class)->during('createForTeam', [
            $team,
            'cluster-identifier',
            'dsn'
        ]);
    }

    function it_does_not_throw_anything_if_the_billing_is_disabled(ClusterCreator $decorated, FlagResolver $flagResolver)
    {
        $team = new Team('slug', 'Name');
        $cluster = new Kubernetes('identifier', 'address', 'version');

        $decorated->createForTeam($team, 'cluster-identifier', 'dsn')->willReturn($cluster);

        $flagResolver->isEnabled(Flags::BILLING)->willReturn(false);

        $this->createForTeam($team, 'cluster-identifier', 'dsn')->shouldReturn($cluster);
    }
}
