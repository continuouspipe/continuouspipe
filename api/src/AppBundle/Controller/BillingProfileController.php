<?php

namespace AppBundle\Controller;

use ContinuousPipe\Billing\ActivityTracker\ActivityTracker;
use ContinuousPipe\Billing\BillingProfile\Trial\TrialResolver;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Billing\Subscription\Subscription;
use ContinuousPipe\Billing\Subscription\SubscriptionClient;
use ContinuousPipe\Billing\Subscription\SubscriptionException;
use ContinuousPipe\Billing\Usage\UsageTracker;
use ContinuousPipe\Message\UserActivity;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route(service="app.controller.billing_profile")
 */
class BillingProfileController
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
     * @var ActivityTracker
     */
    private $activityTracker;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var UsageTracker
     */
    private $usageTracker;
    /**
     * @var string
     */
    private $recurlySubdomain;

    public function __construct(
        UserBillingProfileRepository $userBillingProfileRepository,
        SubscriptionClient $subscriptionClient,
        TrialResolver $trialResolver,
        ActivityTracker $activityTracker,
        UrlGeneratorInterface $urlGenerator,
        UsageTracker $usageTracker,
        string $recurlySubdomain
    ) {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
        $this->subscriptionClient = $subscriptionClient;
        $this->recurlySubdomain = $recurlySubdomain;
        $this->trialResolver = $trialResolver;
        $this->activityTracker = $activityTracker;
        $this->urlGenerator = $urlGenerator;
        $this->usageTracker = $usageTracker;
    }

    /**
     * @Route("/account/billing-profile", name="account_billing_profile_legacy")
     */
    public function billingProfileLegacyAction()
    {
        return new RedirectResponse($this->urlGenerator->generate('account_billing_profiles'));
    }

    /**
     * @Route("/account/billing-profiles", name="account_billing_profiles")
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @Template
     */
    public function billingProfilesAction(User $user, Request $request)
    {
        $billingProfiles = $this->userBillingProfileRepository->findAllByUser($user);
        if (count($billingProfiles) == 0) {
            $this->createBillingProfile($user, $user->getUsername());
        }

        if ($request->isMethod('POST')) {
            $this->createBillingProfile($user, $request->get('name'));
        }

        return ['billingProfiles' => $this->userBillingProfileRepository->findAllByUser($user)];
    }

    /**
     * @Route("/account/billing-profile/{uuid}", name="account_billing_profile")
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @Template
     */
    public function configureAction(User $user, string $uuid, Request $request)
    {
        $billingProfile = $this->userBillingProfileRepository->find(Uuid::fromString($uuid));
        $billingProfileTeams = $this->userBillingProfileRepository->findRelations($billingProfile->getUuid());

        $activities = [];
        foreach ($billingProfileTeams as $team) {
            $activities = array_merge($activities, $this->activityTracker->findBy($team, new \DateTime('-30 days'), new \DateTime()));
        }

        usort($activities, function (UserActivity $left, UserActivity $right) {
            return $left->getDateTime() > $right->getDateTime() ? -1 : 1;
        });

        // Load subscriptions
        $subscriptions = $this->subscriptionClient->findSubscriptionsForBillingProfile($billingProfile);

        if ($request->isMethod('POST')) {
            $this->userBillingProfileRepository->save($billingProfile);
            $operation = $request->request->get('_operation');

            if ('subscribe' === $operation) {
                // Redirect to the subscription page
                return new RedirectResponse(sprintf(
                    'https://%s.recurly.com/subscribe/%s/%s/%s?%s',
                    $this->recurlySubdomain,
                    'single-user', // Plan name
                    $billingProfile->getUuid()->toString(),
                    urlencode($user->getUsername()),
                    http_build_query([
                        'quantity' => $request->request->get('quantity', 1),
                        'email' => $user->getEmail()
                    ])
                ));
            } elseif ('cancel' === $operation || 'update' == $operation) {
                $subscriptionUuid = $request->request->get('_subscription_uuid');
                $matchingSubscriptions = array_filter($subscriptions, function (Subscription $subscription) use ($subscriptionUuid) {
                    return $subscription->getUuid() == $subscriptionUuid;
                });

                if (count($matchingSubscriptions) != 1) {
                    $request->getSession()->getFlashBag()->add('warning', 'You can\'t cancel this subscription');
                } else {
                    $subscriptionIndex = current(array_keys($subscriptions));
                    $subscription = $subscriptions[$subscriptionIndex];

                    try {
                        if ('cancel' == $operation) {
                            $this->subscriptionClient->cancel($billingProfile, $subscription);
                            $request->getSession()->getFlashBag()->add('success', 'Subscription successfully cancelled');
                        } elseif ('update' == $operation) {
                            $subscription = $subscription->withQuantity(
                                $request->request->get('quantity', $subscription->getQuantity())
                            );

                            $this->subscriptionClient->update($billingProfile, $subscription);
                            $request->getSession()->getFlashBag()->add('success', 'Subscription successfully updated');

                            $subscriptions[$subscriptionIndex] = $subscription;
                        }
                    } catch (SubscriptionException $e) {
                        $request->getSession()->getFlashBag()->add('danger', $e->getMessage());
                    }
                }

                return new RedirectResponse($this->urlGenerator->generate('account_billing_profile'));
            }
        }

        return [
            'billingProfile' => $billingProfile,
            'subscriptions' => $subscriptions,
            'usage' => $this->usageTracker->getUsage($billingProfile->getUuid(), new \DateTime('-30 days'), new \DateTime()),
            'trialExpiration' => $this->trialResolver->getTrialPeriodExpirationDate($billingProfile),
            'billingProfileTeams' => $billingProfileTeams,
            'userActivities' => $activities,
            'activityPerDay' => $this->activityPerDay($activities, new \DateTime('-30 days'), new \DateTime()),
        ];
    }

    private function activityPerDay(array $activities, \DateTime $start, \DateTimeInterface $end) : array
    {
        $perDay = [];
        $cursor = $start;

        while ($cursor < $end) {
            $perDay[] = [
                'date' => clone $cursor,
                'count' => count(array_filter($activities, function (UserActivity $activity) use ($cursor) {
                    return $activity->getDateTime()->format('d/m/Y') == $cursor->format('d/m/Y');
                }))
            ];

            $cursor = $cursor->add(new \DateInterval('P1D'));
        }

        return $perDay;
    }

    /**
     * @param User $user
     * @param string $name
     *
     * @return UserBillingProfile
     */
    private function createBillingProfile(User $user, string $name): UserBillingProfile
    {
        $billingProfile = new UserBillingProfile(
            Uuid::uuid4(),
            $user,
            $name,
            new \DateTime(),
            true
        );

        $this->userBillingProfileRepository->save($billingProfile);

        return $billingProfile;
    }
}
