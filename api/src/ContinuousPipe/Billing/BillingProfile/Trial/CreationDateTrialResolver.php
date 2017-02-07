<?php

namespace ContinuousPipe\Billing\BillingProfile\Trial;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;

class CreationDateTrialResolver implements TrialResolver
{
    public function getTrialPeriodExpirationDate(UserBillingProfile $billingProfile): \DateTimeInterface
    {
        if (!$billingProfile->hasTrial() || null === $billingProfile->getCreationDate()) {
            return new \DateTimeImmutable('yesterday');
        }

        $date = \DateTime::createFromFormat('U', $billingProfile->getCreationDate()->getTimestamp());

        return $date->add(new \DateInterval('P14D'));
    }
}
