<?php

namespace ContinuousPipe\River\CodeRepository\Caches;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Doctrine\Common\Cache\Cache;

class CachedFileSystemResolver implements FileSystemResolver
{
    /**
     * @var FileSystemResolver
     */
    private $decoratedResolver;
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var int
     */
    private $lifeTime;

    /**
     * @param FileSystemResolver $decoratedResolver
     * @param Cache $cache
     * @param int $lifeTime
     */
    public function __construct(FileSystemResolver $decoratedResolver, Cache $cache, int $lifeTime = 1800)
    {
        $this->decoratedResolver = $decoratedResolver;
        $this->cache = $cache;
        $this->lifeTime = $lifeTime;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSystem(FlatFlow $flow, CodeReference $codeReference): RelativeFileSystem
    {
        $fileSystem = $this->decoratedResolver->getFileSystem($flow, $codeReference);

        if (null !== ($sha1 = $codeReference->getCommitSha())) {
            $fileSystem = new CachedFileSystem(
                $fileSystem,
                $this->cache,
                'filesystem:' . $flow->getUuid()->toString() . ':' . $sha1,
                $this->lifeTime
            );
        }

        return $fileSystem;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return $this->decoratedResolver->supports($flow);
    }
}
