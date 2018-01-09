<?php

namespace ContinuousPipe\Alerts\Billing;

use ContinuousPipe\Alerts\Alert;
use ContinuousPipe\Alerts\AlertAction;
use ContinuousPipe\Alerts\AlertFinder;
use ContinuousPipe\Billing\BillingProfile\Trial\TrialResolver;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Billing\Subscription\Subscription;
use ContinuousPipe\Billing\Subscription\SubscriptionClient;
use ContinuousPipe\Billing\Usage\UsageTracker;
use ContinuousPipe\Platform\FeatureFlag\FlagResolver;
use ContinuousPipe\Platform\FeatureFlag\Flags;
use ContinuousPipe\Security\Team\Team;

class TeamBillingProfileAlertFinder implements AlertFinder
{
    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;
    /**
     * @var FlagResolver
     */
    private $flagResolver;

    public function __construct(
        UserBillingProfileRepository $userBillingProfileRepository,
        FlagResolver $flagResolver
    ) {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
        $this->flagResolver = $flagResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team): array
    {
        if (!$this->flagResolver->isEnabled(Flags::BILLING)) {
            return [];
        }

        try {
            $billingProfile = $this->userBillingProfileRepository->findByTeam($team);
        } catch (UserBillingProfileNotFound $e) {
            return [
                new Alert(
                    'billing-profile-not-found',
                    'The project does not have any billing profile. You\'ll have a very limited experience.',
                    new \DateTime(),
                    new AlertAction(
                        'state',
                        'Configure the project',
                        'configuration'
                    )
                ),
            ];
        }

        if ($billingProfile->getStatus() != UserBillingProfile::STATUS_ACTIVE) {
            return [
                new Alert(
                    'billing-profile-invalid',
                    'Your billing profile is not active, your experience will be limited.',
                    new \DateTime(),
                    new AlertAction(
                        'state',
                        'Configure billing',
                        'configuration'
                    )
                ),
            ];
        }

        return [];
    }
}
