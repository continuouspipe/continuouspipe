<?php

namespace ContinuousPipe\River\CodeRepository\Caches;

use ContinuousPipe\River\CodeRepository\ChangesComparator;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Doctrine\Common\Cache\Cache;

class CachedChangesComparator implements ChangesComparator
{
    /**
     * @var ChangesComparator
     */
    private $decoratedComparator;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var int
     */
    private $lifetime;

    /**
     * @param ChangesComparator $decoratedComparator
     * @param Cache $cache
     * @param int $lifetime
     */
    public function __construct(ChangesComparator $decoratedComparator, Cache $cache, int $lifetime = 1800)
    {
        $this->decoratedComparator = $decoratedComparator;
        $this->cache = $cache;
        $this->lifetime = $lifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function listChangedFiles(FlatFlow $flow, string $base, string $head): array
    {
        $cacheKey = $flow->getUuid()->toString().'-'.md5($base.':'.$head);

        if (false === ($cachedFiles = $this->cache->fetch($cacheKey))) {
            $cachedFiles = \GuzzleHttp\json_encode(
                $this->decoratedComparator->listChangedFiles($flow, $base, $head)
            );

            $this->cache->save($cacheKey, $cachedFiles, $this->lifetime);
        }

        return \GuzzleHttp\json_decode($cachedFiles, true);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return $this->decoratedComparator->supports($flow);
    }
}
