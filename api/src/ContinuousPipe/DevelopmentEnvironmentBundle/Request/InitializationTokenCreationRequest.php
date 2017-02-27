<?php

namespace ContinuousPipe\DevelopmentEnvironmentBundle\Request;

use JMS\Serializer\Annotation as JMS;

class InitializationTokenCreationRequest
{
    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("git_branch")
     *
     * @var string
     */
    private $gitBranch;

    /**
     * @return string
     */
    public function getGitBranch(): string
    {
        return $this->gitBranch;
    }
}
