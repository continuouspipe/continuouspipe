<?php

namespace ContinuousPipe\Builder\GitHub\Archive;

use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Repository;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use LogStream\Logger;

class InjectGitHubTokenBuilderDecorator implements ArchiveBuilder
{
    /**
     * @var ArchiveBuilder
     */
    private $decoratedBuilder;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param ArchiveBuilder   $decoratedBuilder
     * @param BucketRepository $bucketRepository
     */
    public function __construct(ArchiveBuilder $decoratedBuilder, BucketRepository $bucketRepository)
    {
        $this->decoratedBuilder = $decoratedBuilder;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(BuildRequest $buildRequest, Logger $logger)
    {
        $repository = $buildRequest->getRepository();

        if (null !== $repository && null === $repository->getToken()) {
            $token = $this->getGitHubToken($buildRequest);

            $buildRequest = new BuildRequest(
                new Repository(
                    $repository->getAddress(),
                    $repository->getBranch(),
                    $token
                ),
                $buildRequest->getImage(),
                $buildRequest->getContext(),
                $buildRequest->getNotification(),
                $buildRequest->getLogging(),
                $buildRequest->getEnvironment()
            );
        }

        return $this->decoratedBuilder->getArchive($buildRequest, $logger);
    }

    /**
     * @param BuildRequest $buildRequest
     *
     * @throws ArchiveCreationException
     *
     * @return string
     */
    private function getGitHubToken(BuildRequest $buildRequest)
    {
        try {
            $bucket = $this->bucketRepository->find($buildRequest->getCredentialsBucket());
        } catch (BucketNotFound $e) {
            throw new ArchiveCreationException('Credentials bucket not found', $e->getCode(), $e);
        }

        $tokens = $bucket->getGitHubTokens();
        if (0 === $tokens->count()) {
            throw new ArchiveCreationException(sprintf(
                'No GitHub token found in bucket "%s"',
                $bucket->getUuid()
            ));
        }

        return $tokens->first()->getAccessToken();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(BuildRequest $request)
    {
        return $this->decoratedBuilder->supports($request);
    }
}
