<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\Organisation as OrganisationInterface;
use GitHub\WebHook\Model\Organisation;

class GitHubOrganisation implements OrganisationInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string|null
     */
    private $avatarUrl;

    public function __construct(string $identifier, string $avatarUrl = null)
    {
        $this->identifier = $identifier;
        $this->avatarUrl = $avatarUrl;
    }

    public static function fromGitHubOrganisation(Organisation $organisation)
    {
        return new self(
            $organisation->getLogin(),
            $organisation->getAvatarUrl()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier() : string
    {
        return $this->identifier;
    }
}
