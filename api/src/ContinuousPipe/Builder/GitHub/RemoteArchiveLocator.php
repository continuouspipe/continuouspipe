<?php

namespace ContinuousPipe\Builder\GitHub;

use ContinuousPipe\Builder\Repository;

class RemoteArchiveLocator
{
    /**
     * @var RepositoryAddressDescriptor
     */
    private $repositoryAddressDescriptor;

    /**
     * @param RepositoryAddressDescriptor $repositoryAddressDescriptor
     */
    public function __construct(RepositoryAddressDescriptor $repositoryAddressDescriptor)
    {
        $this->repositoryAddressDescriptor = $repositoryAddressDescriptor;
    }

    /**
     * @param Repository $repository
     *
     * @return string
     *
     * @throws InvalidRepositoryAddress
     */
    public function getArchiveUrl(Repository $repository)
    {
        $description = $this->repositoryAddressDescriptor->getDescription($repository->getAddress());

        return sprintf(
            'https://github.com/%s/%s/archive/%s.tar.gz',
            $description->getUsername(),
            $description->getRepository(),
            $repository->getBranch()
        );
    }
}
