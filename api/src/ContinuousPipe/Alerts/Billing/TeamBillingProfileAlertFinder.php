<?php

namespace ContinuousPipe\Alerts\Billing;

use ContinuousPipe\Alerts\Alert;
use ContinuousPipe\Alerts\AlertAction;
use ContinuousPipe\Alerts\AlertFinder;
use ContinuousPipe\Billing\BillingProfile\Trial\TrialResolver;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Billing\Subscription\Subscription;
use ContinuousPipe\Billing\Subscription\SubscriptionClient;
use ContinuousPipe\Security\Team\Team;

class TeamBillingProfileAlertFinder implements AlertFinder
{
    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;
    /**
     * @var SubscriptionClient
     */
    private $subscriptionClient;
    /**
     * @var TrialResolver
     */
    private $trialResolver;

    public function __construct(
        UserBillingProfileRepository $userBillingProfileRepository,
        SubscriptionClient $subscriptionClient,
        TrialResolver $trialResolver
    ) {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
        $this->subscriptionClient = $subscriptionClient;
        $this->trialResolver = $trialResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team): array
    {
        try {
            $billingProfile = $this->userBillingProfileRepository->findByTeam($team);
        } catch (UserBillingProfileNotFound $e) {
            return [
                new Alert(
                    'billing-profile-not-found',
                    'The team does not have any billing profile. You\'ll have a very limited experience.',
                    new \DateTime(),
                    new AlertAction(
                        'state',
                        'Configure the team',
                        'configuration'
                    )
                ),
            ];
        }

        $alerts = [];

        $now = new \DateTime();
        $trialExpiration = $this->trialResolver->getTrialPeriodExpirationDate($billingProfile);
        if ($trialExpiration > $now) {
            $alerts[] = new Alert(
                'billing-profile-trial',
                sprintf('Your trial period is ending in %d days.', $now->diff($trialExpiration)->format('%a')),
                new \DateTime(),
                new AlertAction(
                    'href',
                    'Manage by billing',
                    'https://authenticator.continuouspipe.io/account/billing/'
                )
            );
        } else {
            $subscriptions = $this->subscriptionClient->findSubscriptionsForBillingProfile($billingProfile);
            if (0 === count($subscriptions)) {
                $alerts[] = new Alert(
                    'billing-profile-has-no-subscription',
                    'The team billing profile do not have any subcription. You\'ll have a very limited experience.',
                    new \DateTime(),
                    new AlertAction(
                        'state',
                        'Configure the team',
                        'configuration'
                    )
                );
            } elseif (!$this->hasActiveSubscription($subscriptions)) {
                $alerts[] = new Alert(
                    'billing-profile-has-no-active-subscription',
                    'The team billing profile subscription is not active. You\'ll have a very limited experience.',
                    new \DateTime(),
                    new AlertAction(
                        'state',
                        'Configure the team',
                        'configuration'
                    )
                );
            }
        }

        return $alerts;
    }

    private function hasActiveSubscription(array $subscriptions) : bool
    {
        return array_reduce($subscriptions, function (bool $carry, Subscription $subscription) {
            return $carry || $subscription->getState() == Subscription::STATE_ACTIVE;
        }, false);
    }
}
