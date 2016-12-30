<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class PullRequestReference
{
    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Repository")
     *
     * @var Repository
     */
    private $repository;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Commit")
     *
     * @var Commit
     */
    private $commit;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Branch")
     *
     * @var Branch
     */
    private $branch;

    /**
     * @return Repository
     */
    public function getRepository(): Repository
    {
        return $this->repository;
    }

    /**
     * @return Commit
     */
    public function getCommit(): Commit
    {
        return $this->commit;
    }

    /**
     * @return Branch
     */
    public function getBranch(): Branch
    {
        return $this->branch;
    }
}
