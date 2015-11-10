<?php

namespace ContinuousPipe\River\Task\Deploy\Naming;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\River\CodeReference;
use Rhumsaa\Uuid\Uuid;

class LimitedLengthNamingStrategy implements EnvironmentNamingStrategy
{
    /**
     * @var EnvironmentNamingStrategy
     */
    private $namingStrategy;

    /**
     * @param EnvironmentNamingStrategy $namingStrategy
     */
    public function __construct(EnvironmentNamingStrategy $namingStrategy)
    {
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(Uuid $flowUuid, CodeReference $codeReference)
    {
        $name = $this->namingStrategy->getName($flowUuid, $codeReference);

        if (strlen($name) > 63) {
            // Already 37 chars
            $name = ((string) $flowUuid).'-';
            $branchIdentifier = $codeReference->getBranch();

            $name .= substr($branchIdentifier, 0, 63 - strlen($name) - 11).'-';
            $name .= substr(md5($branchIdentifier), 0, 63 - strlen($name));
        }

        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnvironmentPartOfFlow(Uuid $flowUuid, Environment $environment)
    {
        return $this->namingStrategy->isEnvironmentPartOfFlow($flowUuid, $environment);
    }
}
