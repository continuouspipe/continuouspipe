<?php

namespace ContinuousPipe\River\Filter;

use Doctrine\Common\Cache\Cache;
use Ramsey\Uuid\UuidInterface;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Tide;

class CachedContextFactory implements ContextFactory
{
    /**
     * @var ContextFactory
     */
    private $decoratedContext;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(ContextFactory $decoratedContext, Cache $cache)
    {
        $this->decoratedContext = $decoratedContext;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function create(UuidInterface $flowUuid, CodeReference $codeReference, Tide $tide = null) : Tide\Configuration\ArrayObject
    {
        if (null === $tide) {
            return $this->decoratedContext->create($flowUuid, $codeReference);
        }

        $cacheKey = "tide-filter-context:" . $tide->getUuid()->toString();

        if (false === ($cachedData = $this->cache->fetch($cacheKey))) {
            $cachedData = $this->decoratedContext->create($flowUuid, $codeReference, $tide);
            $this->cache->save($cacheKey, $cachedData, 3600);
        }

        return $cachedData;
    }
}
