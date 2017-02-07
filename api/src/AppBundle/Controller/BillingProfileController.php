<?php

namespace AppBundle\Controller;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Billing\Subscription\Subscription;
use ContinuousPipe\Billing\Subscription\SubscriptionClient;
use ContinuousPipe\Billing\Subscription\SubscriptionException;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @var string
     */
    private $recurlySubdomain;

    public function __construct(
        UserBillingProfileRepository $userBillingProfileRepository,
        SubscriptionClient $subscriptionClient,
        string $recurlySubdomain
    ) {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
        $this->subscriptionClient = $subscriptionClient;
        $this->recurlySubdomain = $recurlySubdomain;
    }

    /**
     * @Route("/account/billing-profile", name="account_billing_profile")
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @Template
     */
    public function configureAction(User $user, Request $request)
    {
        try {
            $billingProfile = $this->userBillingProfileRepository->findByUser($user);
        } catch (UserBillingProfileNotFound $e) {
            $billingProfile = new UserBillingProfile(
                Uuid::uuid4(),
                $user,
                sprintf('%s (%s)', $user->getUsername(), $user->getEmail()),
                new \DateTime(),
                true
            );
        }

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
            }
        }

        return [
            'billingProfile' => $billingProfile,
            'subscriptions' => $subscriptions,
        ];
    }
}
