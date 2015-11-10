<?php

namespace ContinuousPipe\River\Task\Deploy\Naming;

use Cocur\Slugify\Slugify;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\River\CodeReference;
use Rhumsaa\Uuid\Uuid;

class FlowUuidAndBranchNameStrategy implements EnvironmentNamingStrategy
{
    /**
     * {@inheritdoc}
     */
    public function getName(Uuid $flowUuid, CodeReference $codeReference)
    {
        $branch = (new Slugify())->slugify($codeReference->getBranch());

        return sprintf('%s-%s', (string) $flowUuid, $branch);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnvironmentPartOfFlow(Uuid $flowUuid, Environment $environment)
    {
        return strpos($environment->getName(), (string) $flowUuid) === 0;
    }
}
