<?php

namespace ContinuousPipe\Billing\BillingProfile\WhenUserIsCreated;

use ContinuousPipe\Authenticator\Security\Event\UserCreated;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use Ramsey\Uuid\Uuid;

class CreateABillingProfile
{
    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;

    public function __construct(UserBillingProfileRepository $userBillingProfileRepository)
    {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
    }

    public function notify(UserCreated $event)
    {
        $this->userBillingProfileRepository->save(new UserBillingProfile(
            Uuid::uuid4(),
            $event->getUser(),
            $event->getUser()->getUsername(),
            new \DateTime(),
            true
        ));
    }
}
