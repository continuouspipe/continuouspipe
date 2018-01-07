<?php

namespace ContinuousPipe\River\Task\Build;

use ContinuousPipe\River\Context;
use ContinuousPipe\River\Task\TaskContext;

class BuildContext extends TaskContext
{
    const ENVIRONMENT_KEY = 'environment';

    /**
     * @param Context $parent
     *
     * @return BuildContext
     */
    public static function createBuildContext(Context $parent)
    {
        $context = new self($parent);

        return $context;
    }

    /**
     * @return array
     */
    public function getEnvironment()
    {
        return $this->get(self::ENVIRONMENT_KEY) ?: [];
    }
}
