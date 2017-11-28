<?php

namespace ContinuousPipe\Billing\Plan;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;

class ChangeBillingPlanResponse
{
    /**
     * @var UserBillingProfile
     */
    private $billingProfile;

    /**
     * @var string
     */
    private $redirectUrl;

    public function __construct(UserBillingProfile $billingProfile, string $redirectUrl = null)
    {
        $this->billingProfile = $billingProfile;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return UserBillingProfile
     */
    public function getBillingProfile(): UserBillingProfile
    {
        return $this->billingProfile;
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
