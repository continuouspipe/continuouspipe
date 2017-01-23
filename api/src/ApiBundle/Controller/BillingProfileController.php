<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Security\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="api.controller.billing_profile")
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
     * @Route("/me/billing-profile", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @View
     */
    public function getAction(User $user)
    {
        return $this->userBillingProfileRepository->findByUser($user);
    }
}
