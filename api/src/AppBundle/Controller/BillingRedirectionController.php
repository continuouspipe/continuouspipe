<?php

namespace AppBundle\Controller;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Billing\Plan\Recurly\RecurlyPlanManager;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route(service="app.controller.billing_redirection")
 */
class BillingRedirectionController
{
    /**
     * @var UserBillingProfileRepository
     */
    private $billingProfileRepository;

    /**
     * @var RecurlyPlanManager
     */
    private $recurlyPlanManager;

    /**
     * @param UserBillingProfileRepository $billingProfileRepository
     * @param RecurlyPlanManager $recurlyPlanManager
     */
    public function __construct(UserBillingProfileRepository $billingProfileRepository, RecurlyPlanManager $recurlyPlanManager)
    {
        $this->billingProfileRepository = $billingProfileRepository;
        $this->recurlyPlanManager = $recurlyPlanManager;
    }

    /**
     * @Route("/billing-redirection/out", methods={"GET"}, name="billing_redirection_out")
     */
    public function redirectToSubscribeAction(Request $request)
    {
        if (null === ($target = $request->query->get('to'))) {
            throw new BadRequestHttpException('Parameter `to` is required');
        }

        if ($request->hasSession()) {
            $request->getSession()->set('_redirection_target', $request->query->get('from'));
            $request->getSession()->set('_redirection_billing_profile', $request->query->get('billing_profile'));
        }

        return new RedirectResponse($target);
    }

    /**
     * @Route("/billing-redirection/in", methods={"GET"})
     */
    public function redirectFromSubscribeAction(Request $request)
    {
        if ($request->hasSession()) {
            if (null !== ($billingProfileUuid = $request->getSession()->get('_redirection_billing_profile'))) {
                $this->recurlyPlanManager->refreshBillingProfile(
                    $this->billingProfileRepository->find(Uuid::fromString($billingProfileUuid))
                );
            }

            $target = $request->getSession()->get('_redirection_target');
        } else {
            $target = null;
        }

        if (empty($target)) {
            $target = 'https://ui.continuouspipe.io/billing';
        }

        return new RedirectResponse($target);
    }
}
