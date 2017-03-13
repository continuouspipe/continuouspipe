<?php

namespace ContinuousPipe\Authenticator\Intercom\Normalizer;

use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\User\User;
use ContinuousPipe\Billing\BillingProfile\Trial\TrialResolver;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;

class UserNormalizer
{
    /**
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;

    /**
     * @var TrialResolver
     */
    private $trialResolver;

    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingPorfileRepository;

    /**
     * @param TeamMembershipRepository     $teamMembershipRepository
     * @param TrialResolver                $trialResolver
     * @param UserBillingProfileRepository $userBillingProfileRepository
     */
    public function __construct(TeamMembershipRepository $teamMembershipRepository, TrialResolver $trialResolver, UserBillingProfileRepository $userBillingProfileRepository)
    {
        $this->teamMembershipRepository = $teamMembershipRepository;
        $this->trialResolver = $trialResolver;
        $this->userBillingPorfileRepository = $userBillingProfileRepository;
    }

    /**
     * Normalize the user representation.
     *
     * @param User $user
     *
     * @return array
     */
    public function normalize(User $user)
    {
        try {
            $trialExpiryDate = $this->getUserTrialExpiryDate($user);
        } catch (UserBillingProfileNotFound $e) {
            $trialExpiryDate = new \DateTimeImmutable('yesterday');
        }

        return [
            'user_id' => $user->getUsername(),
            'email' => $user->getEmail(),
            'name' => $user->getUsername(),
            'new_session' => true,
            'companies' => $this->teamMembershipRepository->findByUser($user)->map(function (TeamMembership $teamMembership) {
                return [
                    'company_id' => $teamMembership->getTeam()->getSlug(),
                    'name' => $teamMembership->getTeam()->getName(),
                ];
            })->toArray(),
            'custom_attributes' => [
                'in_trial' => $trialExpiryDate >= (new \DateTimeImmutable('today')) ? 'Yes': 'No',
                'trial_ends_at' => $trialExpiryDate->getTimestamp(),
            ],
        ];
    }

    /**
     * @param User $user
     *
     * @return \DateTimeInterface
     */
    private function getUserTrialExpiryDate(User $user) : \DateTimeInterface
    {
        return $this->trialResolver->getTrialPeriodExpirationDate(
            $this->userBillingPorfileRepository->findByUser($user)
        );
    }
}
