<?php

namespace ContinuousPipe\River\Tests\CodeRepository\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\View\Tide;
use Ramsey\Uuid\UuidInterface;

class FakePullRequestResolver implements PullRequestResolver
{
    private $resolution = [];
    /**
     * @var PullRequestResolver
     */
    private $innerResolver;

    /**
     * FakePullRequestResolver constructor.
     */
    public function __construct(PullRequestResolver $innerResolver)
    {
        $this->innerResolver = $innerResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function findPullRequestWithHeadReference(UuidInterface $flowUuid, CodeReference $codeReference) : array
    {
        if (count($this->resolution) > 0) {
            return $this->resolution;
        }

        return $this->innerResolver->findPullRequestWithHeadReference($flowUuid, $codeReference);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UuidInterface $flowUuid, CodeRepository $repository): bool
    {
        return true;
    }

    /**
     * Updates the future resolution.
     *
     * @param array $resolution
     */
    public function willResolve(array $resolution)
    {
        $this->resolution = $resolution;
    }

    /**
     * @return PullRequest[]
     */
    public function findAll(UuidInterface $flowUuid, CodeRepository $repository): array
    {
        if (count($this->resolution) > 0) {
            return $this->resolution;
        }

        return $this->innerResolver->findAll($flowUuid, $repository);
    }
}
