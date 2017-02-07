<?php

namespace ContinuousPipe\Alerts\Billing;

use ContinuousPipe\Alerts\Alert;
use ContinuousPipe\Alerts\AlertAction;
use ContinuousPipe\Alerts\AlertFinder;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Security\Team\Team;

class TeamBillingProfileAlertFinder implements AlertFinder
{
    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;

    public function __construct(UserBillingProfileRepository $userBillingProfileRepository)
    {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team): array
    {
        $alerts = [];

        try {
            $this->userBillingProfileRepository->findByTeam($team);
        } catch (UserBillingProfileNotFound $e) {
            $alerts[] = new Alert(
                'billing-profile-not-found',
                'The team does not have any billing profile. You\'ll have a very limited experience.',
                new \DateTime(),
                new AlertAction(
                    'state',
                    'Configure',
                    'configuration'
                )
            );
        }

        return $alerts;
    }
}
