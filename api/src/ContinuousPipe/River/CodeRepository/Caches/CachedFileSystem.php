<?php

namespace ContinuousPipe\River\CodeRepository\Caches;

use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use Doctrine\Common\Cache\Cache;

class CachedFileSystem implements RelativeFileSystem
{
    /**
     * @var RelativeFileSystem
     */
    private $decoratedFileSystem;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var int
     */
    private $lifeTime;

    public function __construct(RelativeFileSystem $decoratedFileSystem, Cache $cache, string $cacheKey, int $lifeTime)
    {
        $this->decoratedFileSystem = $decoratedFileSystem;
        $this->cache = $cache;
        $this->cacheKey = $cacheKey;
        $this->lifeTime = $lifeTime;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($filePath)
    {
        $cacheKey = $this->fileKey($filePath, 'exists');
        $cachedExists = $this->cache->fetch($cacheKey);

        if (is_array($cachedExists) && isset($cachedExists['exists'])) {
            return $cachedExists['exists'];
        }

        $fileExists = $this->decoratedFileSystem->exists($filePath);
        $this->cache->save($cacheKey, ['exists' => $fileExists], $this->lifeTime);

        return $fileExists;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filePath)
    {
        $cacheKey = $this->fileKey($filePath, 'contents');
        if (false === ($contents = $this->cache->fetch($cacheKey))) {
            $contents = $this->decoratedFileSystem->getContents($filePath);

            $this->cache->save($cacheKey, $contents, $this->lifeTime);
        }

        return $contents;
    }

    private function fileKey(string $filePath, string $context) : string
    {
        return $this->cacheKey.':'.$context.':'.md5($filePath);
    }
}
