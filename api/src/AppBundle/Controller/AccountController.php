<?php

namespace AppBundle\Controller;

use ContinuousPipe\Billing\ActivityTracker\ActivityTracker;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Message\UserActivity;
use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\AccountRepository;
use ContinuousPipe\Security\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route(path="/account", service="app.controller.account")
 */
class AccountController
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;

    /**
     * @var ActivityTracker
     */
    private $activityTracker;

    public function __construct(
        AccountRepository $accountRepository,
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        UserBillingProfileRepository $userBillingProfileRepository,
        ActivityTracker $activityTracker
    ) {
        $this->accountRepository = $accountRepository;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->userBillingProfileRepository = $userBillingProfileRepository;
        $this->activityTracker = $activityTracker;
    }

    /**
     * @Route("/", name="account")
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @Template
     */
    public function overviewAction(User $user)
    {
        try {
            $billingProfile = $this->userBillingProfileRepository->findByUser($user);
            $billingProfileTeams = $this->userBillingProfileRepository->findRelations($billingProfile);

            $activities = [];
            foreach ($billingProfileTeams as $team) {
                $activities = array_merge($activities, $this->activityTracker->findBy($team, new \DateTime('-30 days'), new \DateTime()));
            }

            usort($activities, function (UserActivity $left, UserActivity $right) {
                return $left->getDateTime() > $right->getDateTime() ? -1 : 1;
            });
        } catch (UserBillingProfileNotFound $e) {
            $billingProfile = null;
            $billingProfileTeams = [];
            $activities = [];
        }

        return [
            'user' => $user,
            'accounts' => $this->accountRepository->findByUsername($user->getUsername()),
            'billingProfile' => $billingProfile,
            'billingProfileTeams' => $billingProfileTeams,
            'userActivities' => $activities,
        ];
    }

    /**
     * @Route("/unlink/{accountUuid}", name="unlink_account")
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @ParamConverter("account", converter="account", options={"uuid"="accountUuid"})
     * @Template
     */
    public function unlinkAction(Request $request, User $user, Account $account)
    {
        $form = $this->formFactory->createBuilder('form')
            ->setMethod('POST')
            ->getForm()
            ->handleRequest($request)
        ;

        if ($form->isValid()) {
            $this->accountRepository->unlink($user->getUsername(), $account);

            $request->getSession()->getFlashBag()->add(
                'success',
                'The account has been unlinked'
            );

            return new RedirectResponse($this->urlGenerator->generate('account'));
        }

        return [
            'form' => $form->createView(),
            'user' => $user,
            'account' => $account,
        ];
    }
}
