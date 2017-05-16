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
    /**
     * @var UsageTracker
     */
    private $usageTracker;

    public function __construct(
        UserBillingProfileRepository $userBillingProfileRepository,
        SubscriptionClient $subscriptionClient,
        TrialResolver $trialResolver,
        UsageTracker $usageTracker
    ) {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
        $this->subscriptionClient = $subscriptionClient;
        $this->trialResolver = $trialResolver;
        $this->usageTracker = $usageTracker;
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

        if (null === ($subscriptionAlert = $this->getSubscriptionAlert($billingProfile))) {
            return [];
        }

        $now = new \DateTime();
        $trialExpiration = $this->trialResolver->getTrialPeriodExpirationDate($billingProfile);
        if ($trialExpiration > $now) {
            return [
                new Alert(
                    'billing-profile-trial',
                    sprintf('Your trial period is ending in %d days.', $now->diff($trialExpiration)->format('%a')),
                    new \DateTime(),
                    new AlertAction(
                        'link',
                        'Manage my billing',
                        'https://authenticator.continuouspipe.io/account/billing-profile'
                    )
                ),
            ];
        }

        return [
            $subscriptionAlert,
        ];
    }

    /**
     * @param Subscription[] $subscriptions
     *
     * @return Subscription[]
     */
    private function getActiveSubscription(array $subscriptions) : array
    {
        return array_filter($subscriptions, function (Subscription $subscription) {
            return $subscription->getState() == Subscription::STATE_ACTIVE;
        });
    }

    /**
     * @param UserBillingProfile $billingProfile
     *
     * @return Alert|null
     */
    private function getSubscriptionAlert(UserBillingProfile $billingProfile)
    {
        $subscriptions = $this->subscriptionClient->findSubscriptionsForBillingProfile($billingProfile);

        if (0 === count($subscriptions)) {
            return new Alert(
                'billing-profile-has-no-subscription',
                'Your project billing profile does not have any subscriptions. This will limit your experience.',
                new \DateTime(),
                new AlertAction(
                    'state',
                    'Configure the project',
                    'configuration'
                )
            );
        }

        $activeSubscriptions = $this->getActiveSubscription($subscriptions);
        if (count($activeSubscriptions) == 0) {
            return new Alert(
                'billing-profile-has-no-active-subscription',
                'Your project billing profile does not have any active subscriptions. This will limit your experience.',
                new \DateTime(),
                new AlertAction(
                    'state',
                    'Configure the project',
                    'configuration'
                )
            );
        }

        $allowedActiveUsers = array_reduce($activeSubscriptions, function (int $carry, Subscription $subscription) {
            return $carry + $subscription->getQuantity();
        }, 0);

        /** @var Subscription $principalSubscription */
        $principalSubscription = current($activeSubscriptions);
        $usage = $this->usageTracker->getUsage($billingProfile->getUuid(), $principalSubscription->getCurrentBillingPeriodStartedAt(), $principalSubscription->getCurrentBillingPeriodEndsAt());

        if ($usage->getNumberOfActiveUsers() > $allowedActiveUsers) {
            return new Alert(
                'usage-over-subscription',
                sprintf(
                    'We\'ve identified %d active users in this billing period while your subscription is for %d users.',
                    $usage->getNumberOfActiveUsers(),
                    $allowedActiveUsers
                ),
                new \DateTime(),
                new AlertAction(
                    'link',
                    'Upgrade',
                    'https://authenticator.continuouspipe.io/account/billing-profile'
                )
            );
        }

        return null;
    }
}
