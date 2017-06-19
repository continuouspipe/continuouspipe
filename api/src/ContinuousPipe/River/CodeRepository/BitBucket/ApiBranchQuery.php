<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\River\CodeRepository\Branch;
use ContinuousPipe\River\CodeRepository\BranchQuery;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use GuzzleHttp\Psr7;

class ApiBranchQuery implements BranchQuery
{
    private $clientFactory;

    public function __construct(BitBucketClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * @return Branch[]
     */
    public function findBranches(FlatFlow $flow): array
    {
        $repository = $flow->getRepository();
        if (!$repository instanceof BitBucketCodeRepository) {
            throw new \InvalidArgumentException('The repository of this flow is not supported');
        }

        return $this->clientFactory->createForCodeRepository($repository)->getBranches($repository);
    }
}
