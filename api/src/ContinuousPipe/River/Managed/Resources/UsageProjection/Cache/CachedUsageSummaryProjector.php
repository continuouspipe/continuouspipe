<?php

namespace ContinuousPipe\River\Managed\Resources\UsageProjection\Cache;

use ContinuousPipe\Billing\BillingProfile\BillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Managed\Resources\UsageProjection\UsageSummary;
use ContinuousPipe\River\Managed\Resources\UsageProjection\UsageSummaryProjector;
use Doctrine\Common\Cache\Cache;

class CachedUsageSummaryProjector implements UsageSummaryProjector
{
    /**
     * @var UsageSummaryProjector
     */
    private $decoratedProjector;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var int
     */
    private $lifeTime;


    public function __construct(UsageSummaryProjector $decoratedProjector, Cache $cache, int $lifeTime = 1800)
    {
        $this->decoratedProjector = $decoratedProjector;
        $this->cache = $cache;
        $this->lifeTime = $lifeTime;
    }

    public function forFlows(array $flows, \DateTime $left, \DateTime $right, \DateInterval $interval, UserBillingProfile $billingProfile = null): UsageSummary
    {
        $cacheKey = sprintf(
            'usage-summary:%s:%s:%s:%s:%s',
            md5(implode(',', array_map(function (FlatFlow $flow) {
                return $flow->getUuid()->toString();
            }, $flows))),
            $left->format(\DateTime::ISO8601),
            $right->format(\DateTime::ISO8601),
            $interval->format('%dd%hh'),
            $billingProfile !== null ? $billingProfile->getUuid()->toString() : 'null'
        );

        if (false === ($serialized = $this->cache->fetch($cacheKey))) {
            $summary = $this->decoratedProjector->forFlows($flows, $left, $right, $interval, $billingProfile);
            $serialized = serialize($summary);

            $this->cache->save($cacheKey, $serialized, $this->lifeTime);
        }

        return unserialize($serialized);
    }
}
