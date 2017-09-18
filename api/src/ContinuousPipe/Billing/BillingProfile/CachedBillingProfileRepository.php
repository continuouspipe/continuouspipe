<?php

namespace ContinuousPipe\Billing\BillingProfile;

use ContinuousPipe\Billing\BillingException;
use ContinuousPipe\Security\Team\Team;
use Doctrine\Common\Cache\Cache;
use JMS\Serializer\SerializerInterface;

class CachedBillingProfileRepository implements BillingProfileRepository
{
    /**
     * @var BillingProfileRepository
     */
    private $decoratedBillingProfile;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var int
     */
    private $lifeTime;

    public function __construct(BillingProfileRepository $decoratedBillingProfile, SerializerInterface $serializer, Cache $cache, int $lifeTime = 1800)
    {
        $this->decoratedBillingProfile = $decoratedBillingProfile;
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->lifeTime = $lifeTime;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team): BillingProfile
    {
        $cacheKey = 'billing-profile-for-team:'.$team->getSlug();
        if (false !== ($serializedBillingProfile = $this->cache->fetch($cacheKey))) {
            try {
                $billingProfile = $this->serializer->deserialize($serializedBillingProfile, BillingProfile::class, 'json');
            } catch (\Exception $e) {
                // We consider this as invalid...
            }
        }

        if (!isset($billingProfile)) {
            $billingProfile = $this->decoratedBillingProfile->findByTeam($team);

            $this->cache->save($cacheKey, $this->serializer->serialize($billingProfile, 'json'), $this->lifeTime);
        }

        return $billingProfile;
    }
}
