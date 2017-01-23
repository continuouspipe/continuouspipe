<?php

namespace ContinuousPipe\Authenticator\Team;

use ContinuousPipe\Authenticator\Event\TeamCreationEvent;
use ContinuousPipe\Authenticator\Team\Request\TeamCreationRequest;
use ContinuousPipe\Authenticator\Team\Request\TeamPartialUpdateRequest;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\User\User;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TeamCreator
{
    /**
     * @var TeamRepository
     */
    private $teamRepository;
    /**
     * @var TeamMembershipRepository
     */
    private $membershipRepository;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;

    public function __construct(
        TeamRepository $teamRepository,
        TeamMembershipRepository $membershipRepository,
        UserBillingProfileRepository $userBillingProfileRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->teamRepository = $teamRepository;
        $this->membershipRepository = $membershipRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->userBillingProfileRepository = $userBillingProfileRepository;
    }

    /**
     * Create a new team.
     *
     * @param TeamCreationRequest $creationRequest
     * @param User $owner
     *
     * @throws TeamCreationException
     *
     * @return Team
     */
    public function create(TeamCreationRequest $creationRequest, User $owner)
    {
        $team = $creationRequest->getTeam();

        $this->eventDispatcher->dispatch(TeamCreationEvent::BEFORE_EVENT, new TeamCreationEvent($team, $owner));

        $team = $this->teamRepository->save($team);
        $this->membershipRepository->save(new TeamMembership($team, $owner, [TeamMembership::PERMISSION_ADMIN]));

        if (null !== ($billingProfile = $creationRequest->getBillingProfile())) {
            $billingProfile = $this->userBillingProfileRepository->find($billingProfile->getUuid());
            if ($billingProfile->getUser()->getUsername() != $owner->getUsername()) {
                throw new TeamCreationException('You are not authorized to use this billing profile');
            }

            $billingProfile->getTeams()->add($team);

            $this->userBillingProfileRepository->save($billingProfile);
        }

        $this->eventDispatcher->dispatch(TeamCreationEvent::AFTER_EVENT, new TeamCreationEvent($team, $owner));

        return $team;
    }

    public function update(Team $team, User $updater, TeamPartialUpdateRequest $updateRequest)
    {
        if (null !== ($teamUpdate = $updateRequest->getTeam())) {
            if ($teamUpdate->getSlug() !== null) {
                throw new TeamCreationException('You cannot update the team slug');
            }

            if ($name = $teamUpdate->getName()) {
                $team = new Team(
                    $team->getSlug(),
                    $name,
                    $team->getBucketUuid(),
                    $team->getMemberships()->toArray()
                );
            }
        }

        if (null !== ($billingProfile = $updateRequest->getBillingProfile())) {
            $billingProfile = $this->userBillingProfileRepository->find($billingProfile->getUuid());
            if ($billingProfile->getUser()->getUsername() != $updater->getUsername()) {
                throw new TeamCreationException('You are not authorized to use this billing profile');
            }

            try {
                $existingBillingProfile = $this->userBillingProfileRepository->findByTeam($team);
                $existingBillingProfile->getTeams()->removeElement($team);

                $this->userBillingProfileRepository->save($existingBillingProfile);
            } catch (UserBillingProfileNotFound $e) {
                // No existing billing profile, ignore...
            }

            $billingProfile->getTeams()->add($team);

            $this->userBillingProfileRepository->save($billingProfile);
        }

        $this->teamRepository->save($team);

        return $team;
    }
}
