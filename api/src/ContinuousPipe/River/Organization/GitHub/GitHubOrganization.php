<?php

namespace ContinuousPipe\River\Organization\GitHub;

use ContinuousPipe\River\Organization as OrganizationInterface;
use GitHub\WebHook\Model\Organization;

class GitHubOrganization implements OrganizationInterface
{
    /**
     * @var Organization
     */
    private $organization;

    /**
     * @param Organization $organization
     */
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->organization->getLogin();
    }

    /**
     * {@inheritdoc}
     */
    public function getReposUrl()
    {
        return $this->organization->getReposUrl();
    }
}
