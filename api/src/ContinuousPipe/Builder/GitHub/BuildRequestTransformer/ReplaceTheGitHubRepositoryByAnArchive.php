<?php

namespace ContinuousPipe\Builder\GitHub\BuildRequestTransformer;

use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\GitHub\InvalidRepositoryAddress;
use ContinuousPipe\Builder\GitHub\RepositoryAddressDescriptor;
use ContinuousPipe\Builder\Request\ArchiveSource;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;
use Psr\Log\LoggerInterface;

class ReplaceTheGitHubRepositoryByAnArchive implements BuildRequestTransformer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(BuildRequest $request) : BuildRequest
    {
        return $request->withSteps(array_map(function (BuildStepConfiguration $buildStepConfiguration) {
            if ($buildStepConfiguration->getArchive() !== null) {
                return $buildStepConfiguration;
            }

            $repository = $buildStepConfiguration->getRepository();

            try {
                $description = (new RepositoryAddressDescriptor())->getDescription($repository->getAddress());
            } catch (InvalidRepositoryAddress $e) {
                $this->logger->warning('The repository can not be replaced by an archive', [
                    'exception' => $e,
                ]);

                return $buildStepConfiguration;
            }

            $archiveUrl = sprintf(
                'https://api.github.com/repos/%s/%s/tarball/%s',
                $description->getUsername(),
                $description->getRepository(),
                $repository->getBranch()
            );

            return $buildStepConfiguration->withArchiveSource(new ArchiveSource(
                $archiveUrl,
                [
                    'Authorization' => 'token '.$repository->getToken(),
                ]
            ));
        }, $request->getSteps()));
    }
}
