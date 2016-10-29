<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository;
use GitHub\WebHook\Model\Repository;
use JMS\Serializer\Annotation as JMS;

class GitHubCodeRepository implements CodeRepository
{
    /**
     * @JMS\Type("GitHub\WebHook\Model\Repository")
     *
     * @var Repository
     */
    private $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return Repository
     */
    public function getGitHubRepository()
    {
        return $this->repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->repository->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress()
    {
        return $this->repository->getUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'github';
    }
}
