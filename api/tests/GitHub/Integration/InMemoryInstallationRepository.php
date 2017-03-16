<?php

namespace GitHub\Integration;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;

class InMemoryInstallationRepository implements InstallationRepository
{
    /**
     * @var Installation[]
     */
    private $installations = [];

    private $apiCallCount = 0;

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->installations;
    }

    /**
     * {@inheritdoc}
     */
    public function findByRepository(GitHubCodeRepository $codeRepository)
    {
        $matchingInstallations = array_filter($this->installations, function(Installation $installation) use ($codeRepository) {
            return $installation->getAccount()->getLogin() == $codeRepository->getOrganisation();
        });

        if (count($matchingInstallations) == 0) {
            throw new InstallationNotFound();
        }

        ++$this->apiCallCount;

        return current($matchingInstallations);
    }

    /**
     * @param Installation $installation
     */
    public function save(Installation $installation)
    {
        $this->installations[$installation->getId()] = $installation;
    }

    public function countApiCalls()
    {
        return $this->apiCallCount;
    }
}
