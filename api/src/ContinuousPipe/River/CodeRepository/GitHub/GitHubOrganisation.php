<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\Organisation as OrganisationInterface;
use GitHub\WebHook\Model\Organisation;

class GitHubOrganisation implements OrganisationInterface
{
    /**
     * @var Organisation
     */
    private $organisation;

    /**
     * @param Organisation $organisation
     */
    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    /**
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->organisation->getLogin();
    }

    /**
     * {@inheritdoc}
     */
    public function getReposUrl()
    {
        return $this->organisation->getReposUrl();
    }
}
