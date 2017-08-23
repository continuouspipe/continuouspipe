<?php

namespace ContinuousPipe\Billing\BillingProfile;

use ContinuousPipe\Billing\BillingProfile\Request\UserBillingProfileCreationRequest;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;

class UserBillingProfileCreator
{
    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;

    public function __construct(UserBillingProfileRepository $userBillingProfileRepository)
    {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
    }

    public function create(
        UserBillingProfileCreationRequest $userBillingProfileCreationRequest,
        User $user
    ): UserBillingProfile {
        $billingProfile = new UserBillingProfile(
            Uuid::uuid4(),
            $userBillingProfileCreationRequest->name,
            new \DateTime(),
            [$user],
            true
        );

        $this->userBillingProfileRepository->save($billingProfile);

        return $billingProfile;
    }
}
