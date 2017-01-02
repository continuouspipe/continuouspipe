<?php

namespace ContinuousPipe\Builder\GitHub\Archive;

use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\GitHub\InvalidRepositoryAddress;
use ContinuousPipe\Builder\GitHub\RepositoryAddressDescriptor;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;

class ReplaceTheGitHubRepositoryByAnArchiveDecorator implements ArchiveBuilder
{
    /**
     * @var ArchiveBuilder
     */
    private $decoratedArchiveBuilder;

    /**
     * @param ArchiveBuilder $decoratedArchiveBuilder
     */
    public function __construct(ArchiveBuilder $decoratedArchiveBuilder)
    {
        $this->decoratedArchiveBuilder = $decoratedArchiveBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(BuildRequest $buildRequest, Logger $logger)
    {
        try {
            $buildRequest = $this->transformBuildRequest($buildRequest);
        } catch (ArchiveCreationException $e) {
            // If we can't then... go through the decorated builder anyway...
        }

        return $this->decoratedArchiveBuilder->getArchive(
            $buildRequest,
            $logger
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(BuildRequest $request)
    {
        try {
            $request = $this->transformBuildRequest($request);
        } catch (ArchiveCreationException $e) {
            // If we can't then... go through the decorated builder anyway...
        }

        return $this->decoratedArchiveBuilder->supports($request);
    }

    /**
     * @param BuildRequest $buildRequest
     *
     * @throws ArchiveCreationException
     *
     * @return BuildRequest
     */
    private function transformBuildRequest(BuildRequest $buildRequest)
    {
        if ($buildRequest->getArchive() === null) {
            $repository = $buildRequest->getRepository();

            try {
                $description = (new RepositoryAddressDescriptor())->getDescription($repository->getAddress());
            } catch (InvalidRepositoryAddress $e) {
                throw new ArchiveCreationException($e->getMessage(), $e->getCode(), $e);
            }

            $archiveUrl = sprintf(
                'https://api.github.com/repos/%s/%s/tarball/%s',
                $description->getUsername(),
                $description->getRepository(),
                $repository->getBranch()
            );

            $buildRequest = new BuildRequest(
                new \ContinuousPipe\Builder\Request\Archive(
                    $archiveUrl,
                    [
                        'Authorization' => 'token '.$repository->getToken(),
                    ]
                ),
                $buildRequest->getImage(),
                $buildRequest->getContext(),
                $buildRequest->getNotification(),
                $buildRequest->getLogging(),
                $buildRequest->getEnvironment()
            );
        }

        return $buildRequest;
    }
}
