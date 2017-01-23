<?php

namespace AppBundle\Controller;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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

    public function __construct(UserBillingProfileRepository $userBillingProfileRepository)
    {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
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
        }

        return [
            'billingProfile' => $billingProfile,
        ];
    }
}
