<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Billing\BillingProfile\Request\UserBillingProfileCreationRequest;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileCreator;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Billing\Subscription\SubscriptionClient;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(service="api.controller.billing_profile")
 */
class BillingProfileController
{
    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;

    /**
     * @var UserBillingProfileCreator
     */
    private $userBillingProfileCreator;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var SubscriptionClient
     */
    private $subscriptionClient;

    public function __construct(
        UserBillingProfileRepository $userBillingProfileRepository,
        UserBillingProfileCreator $userBillingProfileCreator,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        SubscriptionClient $subscriptionClient
    ) {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
        $this->userBillingProfileCreator = $userBillingProfileCreator;
        $this->validator = $validator;
        $this->authorizationChecker = $authorizationChecker;
        $this->subscriptionClient = $subscriptionClient;
    }

    /**
     * @Route("/me/billing-profile", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @View
     */
    public function getAction(User $user)
    {
        return $this->billingProfiles($user);
    }

    /**
     * @Route("/me/billing-profiles", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @View
     */
    public function billingProfiles(User $user)
    {
        $billingProfiles = $this->userBillingProfileRepository->findAllByUser($user);

        if (count($billingProfiles) == 0) {
            throw new UserBillingProfileNotFound(
                sprintf('No billing profiles found for user "%s"', $user->getUsername())
            );
        }

        return $billingProfiles;
    }

    /**
     * @Route("/me/billing-profiles", methods={"POST"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @ParamConverter("creationRequest", converter="fos_rest.request_body")
     * @View(statusCode=201)
     */
    public function createAction(UserBillingProfileCreationRequest $creationRequest, User $user)
    {
        $errors = $this->validator->validate($creationRequest);

        if ($errors->count() > 0) {
            return new JsonResponse(
                [
                    'message' => $errors->get(0)->getMessage(),
                ], 400
            );
        }

        return $this->userBillingProfileCreator->create($creationRequest, $user);
    }

    /**
     * @Route("/billing-profile/{uuid}", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @View
     */
    public function getBillingProfileAction(User $user, string $uuid)
    {
        $billingProfile = $this->userBillingProfileRepository->find(Uuid::fromString($uuid));
        if (!$this->hasAccess($billingProfile)) {
            throw new AccessDeniedHttpException('You are not authorized to access this billing profile');
        }

        return $billingProfile;
    }

    /**
     * @Route("/billing-profile/{uuid}/subscriptions", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @View
     */
    public function getSubscriptionsAction(User $user, string $uuid)
    {
        $billingProfile = $this->userBillingProfileRepository->find(Uuid::fromString($uuid));
        if (!$this->hasAccess($billingProfile)) {
            throw new AccessDeniedHttpException('You are not authorized to access this billing profile');
        }

        return $this->subscriptionClient->findSubscriptionsForBillingProfile($billingProfile);
    }

    private function hasAccess(UserBillingProfile $billingProfile)
    {
        return $this->authorizationChecker->isGranted(['view'], $billingProfile);
    }
}
