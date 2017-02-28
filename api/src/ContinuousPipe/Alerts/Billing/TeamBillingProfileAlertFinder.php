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
                    'link',
                    'Manage by billing',
                    'https://authenticator.continuouspipe.io/account/billing-profile'
                )
            );
        } else {
            $subscriptions = $this->subscriptionClient->findSubscriptionsForBillingProfile($billingProfile);
            if (0 === count($subscriptions)) {
                $alerts[] = new Alert(
                    'billing-profile-has-no-subscription',
                    'The team billing profile does not have any subcription. You\'ll have a very limited experience.',
                    new \DateTime(),
                    new AlertAction(
                        'state',
                        'Configure the team',
                        'configuration'
                    )
                );
            } else {
                $activeSubscriptions = $this->getActiveSubscription($subscriptions);

                if (count($activeSubscriptions) == 0) {
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
                } else {
                    $allowedActiveUsers = array_reduce($activeSubscriptions, function (int $carry, Subscription $subscription) {
                        return $carry + $subscription->getQuantity();
                    }, 0);

                    /** @var Subscription $principalSubscription */
                    $principalSubscription = current($activeSubscriptions);
                    $usage = $this->usageTracker->getUsage($billingProfile->getUuid(), $principalSubscription->getCurrentBillingPeriodStartedAt(), $principalSubscription->getCurrentBillingPeriodEndsAt());

                    if ($usage->getNumberOfActiveUsers() > $allowedActiveUsers) {
                        $alerts[] = new Alert(
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
                }
            }
        }

        return $alerts;
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
}
