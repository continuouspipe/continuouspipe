<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\ChangesComparator;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\RepositoryAddressDescriptor;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\GitHub\GitHubClientException;

class GitHubChangesComparator implements ChangesComparator
{
    /**
     * @var ClientFactory
     */
    private $clientFactory;
    /**
     * @var RepositoryAddressDescriptor
     */
    private $repositoryAddressDescriptor;

    /**
     * @param ClientFactory $clientFactory
     * @param RepositoryAddressDescriptor $repositoryAddressDescriptor
     */
    public function __construct(ClientFactory $clientFactory, RepositoryAddressDescriptor $repositoryAddressDescriptor)
    {
        $this->clientFactory = $clientFactory;
        $this->repositoryAddressDescriptor = $repositoryAddressDescriptor;
    }

    /**
     * {@inheritdoc}
     */
    public function listChangedFiles(FlatFlow $flow, string $base, string $head): array
    {
        $repository = $flow->getRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            throw new \InvalidArgumentException('The repository should only be a GitHub code repository');
        }

        try {
            $repositoryDescription = $this->repositoryAddressDescriptor->getDescription($repository->getAddress());

            $comparison = $this->clientFactory->createClientForFlow($flow->getUuid())->repository()->commits()->compare(
                $repositoryDescription->getUsername(),
                $repositoryDescription->getRepository(),
                $base,
                $head
            );
        } catch (GitHubClientException $e) {
            throw new CodeRepositoryException('Unable to compare the changes from GitHub', $e->getCode(), $e);
        }

        return array_map(function (array $file) {
            return $file['filename'];
        }, $comparison['files']);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return $flow->getRepository() instanceof GitHubCodeRepository;
    }
}
