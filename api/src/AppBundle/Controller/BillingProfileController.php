<?php

namespace AppBundle\Controller;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Billing\Subscription\SubscriptionClient;
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

    public function __construct(
        UserBillingProfileRepository $userBillingProfileRepository,
        SubscriptionClient $subscriptionClient
    ) {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
        $this->subscriptionClient = $subscriptionClient;
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
                sprintf('%s (%s)', $user->getUsername(), $user->getEmail())
            );
        }

        if ($request->isMethod('POST')) {
            $this->userBillingProfileRepository->save($billingProfile);

            // Redirect to the subscription page
            return new RedirectResponse(sprintf(
                'https://continuouspipe.recurly.com/subscribe/%s/%s/%s?%s',
                'single-user', // Plan name
                $billingProfile->getUuid()->toString(),
                urlencode($user->getUsername()),
                http_build_query([
                    'quantity' => $request->request->get('quantity', 1),
                    'email' => $user->getEmail()
                ])
            ));
        }

        return [
            'billingProfile' => $billingProfile,
            'subscriptions' => $this->subscriptionClient->findSubscriptionsForBillingProfile($billingProfile),
        ];
    }
}
