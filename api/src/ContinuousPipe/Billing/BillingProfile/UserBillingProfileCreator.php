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

    /**
     * @param UserBillingProfileCreationRequest $userBillingProfileCreationRequest
     * @param User|User[] $userOrCollection
     * @return UserBillingProfile
     */
    public function create(
        UserBillingProfileCreationRequest $userBillingProfileCreationRequest,
        $userOrCollection
    ): UserBillingProfile {
        $billingProfile = new UserBillingProfile(
            Uuid::uuid4(),
            $userBillingProfileCreationRequest->name,
            new \DateTime(),
            !is_array($userOrCollection) ? [$userOrCollection] : $userOrCollection
        );

        $this->userBillingProfileRepository->save($billingProfile);

        return $billingProfile;
    }
}
