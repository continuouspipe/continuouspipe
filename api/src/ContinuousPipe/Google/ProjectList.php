<?php

namespace ContinuousPipe\Google;

use JMS\Serializer\Annotation as JMS;

final class ProjectList
{
    /**
     * @JMS\Type("array<ContinuousPipe\Google\Project>")
     *
     * @var array|Project[]
     */
    private $projects = [];

    /**
     * @param Project[] $projects
     */
    public function __construct(array $projects)
    {
        $this->projects = $projects;
    }

    /**
     * @return array|Project[]
     */
    public function getProjects()
    {
        return $this->projects;
    }
}
