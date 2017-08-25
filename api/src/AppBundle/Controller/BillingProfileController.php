<?php

namespace AppBundle\Controller;

use ContinuousPipe\Billing\ActivityTracker\ActivityTracker;
use ContinuousPipe\Billing\BillingProfile\Request\UserBillingProfileCreationRequest;
use ContinuousPipe\Billing\BillingProfile\Trial\TrialResolver;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileCreator;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Billing\Subscription\Subscription;
use ContinuousPipe\Billing\Subscription\SubscriptionClient;
use ContinuousPipe\Billing\Subscription\SubscriptionException;
use ContinuousPipe\Billing\Usage\UsageTracker;
use ContinuousPipe\Message\UserActivity;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\User\User;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserBillingProfileCreator
     */
    private $userBillingProfileCreator;

    /**
     * @var string
     */
    private $recurlySubdomain;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        UserBillingProfileRepository $userBillingProfileRepository,
        SubscriptionClient $subscriptionClient,
        TrialResolver $trialResolver,
        ActivityTracker $activityTracker,
        UrlGeneratorInterface $urlGenerator,
        UsageTracker $usageTracker,
        TeamMembershipRepository $teamMembershipRepository,
        LoggerInterface $logger,
        UserBillingProfileCreator $userBillingProfileCreator,
        string $recurlySubdomain
    ) {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
        $this->subscriptionClient = $subscriptionClient;
        $this->recurlySubdomain = $recurlySubdomain;
        $this->trialResolver = $trialResolver;
        $this->activityTracker = $activityTracker;
        $this->urlGenerator = $urlGenerator;
        $this->usageTracker = $usageTracker;
        $this->teamMembershipRepository = $teamMembershipRepository;
        $this->logger = $logger;
        $this->userBillingProfileCreator = $userBillingProfileCreator;
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
        $billingProfiles = $this->userBillingProfileRepository->findByUser($user);
        if (count($billingProfiles) == 0) {
            $this->createBillingProfile($user, $user->getUsername());
        }

        if ($request->isMethod('POST')) {
            $this->createBillingProfile($user, $request->get('name'));
        }

        $billingProfilesOfTeamsUserIsAdmin = $this->teamMembershipRepository->findByUser($user)->filter(function (TeamMembership $membership) {
            return in_array(TeamMembership::PERMISSION_ADMIN, $membership->getPermissions());
        })->map(function (TeamMembership $membership) {
            return $membership->getTeam();
        })->map(function (Team $team) {
            return $this->userBillingProfileRepository->findByTeam($team);
        });

        return [
            'billingProfiles' => $this->userBillingProfileRepository->findByUser($user),
            'relatedTeamsBillingProfiles' => $billingProfilesOfTeamsUserIsAdmin,
        ];
    }

    /**
     * @Route("/account/billing-profile/{uuid}", name="account_billing_profile")
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @ParamConverter("billingProfile", converter="billingProfile")
     * @Security("is_granted('READ', billingProfile)")
     * @Template
     */
    public function configureAction(User $user, UserBillingProfile $billingProfile, Request $request)
    {
        $activities = [];
        foreach ($billingProfile->getTeams() as $team) {
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
                $this->logger->warning('A user has clicked on the subscribe button', [
                    'username' => $user->getUsername(),
                    'billingProfile' => $billingProfile->getUuid()->toString(),
                ]);

                // Add the billing profile in the session
                $request->getSession()->set('_current_billing_profile', $billingProfile->getUuid()->toString());

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

                        $this->logger->error('Something went wrong while changing the billing subscription', [
                            'exception' => $e,
                            'username' => $user->getUsername(),
                            'billingProfile' => $billingProfile->getUuid()->toString(),
                            'operation' => $operation,
                        ]);
                    }

                    $this->logger->warning('A user has changed a subscription', [
                        'username' => $user->getUsername(),
                        'billingProfile' => $billingProfile->getUuid()->toString(),
                        'operation' => $operation,
                    ]);
                }

                return new RedirectResponse($this->urlGenerator->generate('account_billing_profile'));
            }
        }

        return [
            'billingProfile' => $billingProfile,
            'billingProfileTeams' => $billingProfile->getTeams(),
            'subscriptions' => $subscriptions,
            'usage' => $this->usageTracker->getUsage($billingProfile->getUuid(), new \DateTime('-30 days'), new \DateTime()),
            'trialExpiration' => $this->trialResolver->getTrialPeriodExpirationDate($billingProfile),
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
        $userBillingProfileCreationRequest = new userBillingProfileCreationRequest;
        $userBillingProfileCreationRequest->name = $name;

        return $this->userBillingProfileCreator->create($userBillingProfileCreationRequest, $user);
    }
}
