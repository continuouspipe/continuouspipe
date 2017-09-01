<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName;

use ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName\EnvironmentNamingStrategy;
use ContinuousPipe\River\Tide;
use ContinuousPipe\Security\Credentials\Cluster;

class LimitedLengthNamingStrategy implements EnvironmentNamingStrategy
{
    const DEFAULT_MAX_LENGTH = 63;

    /**
     * @var EnvironmentNamingStrategy
     */
    private $namingStrategy;

    /**
     * @var int
     */
    private $maxLength;

    /**
     * @param EnvironmentNamingStrategy $namingStrategy
     * @param int                       $maxLength
     */
    public function __construct(EnvironmentNamingStrategy $namingStrategy, $maxLength = self::DEFAULT_MAX_LENGTH)
    {
        $this->namingStrategy = $namingStrategy;
        $this->maxLength = $maxLength;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(Tide $tide, Cluster $cluster, $expression = null)
    {
        $name = $this->namingStrategy->getName($tide, $cluster, $expression);
        if (strlen($name) <= $this->maxLength) {
            return $name;
        }

        $flowUuid = $tide->getFlowUuid();
        $flowUuidLength = strlen((string) $flowUuid);
        $strippedName = substr($name, 0, $flowUuidLength + 1);
        $branchIdentifier = substr($name, strlen($strippedName));

        $hashLength = 10;
        $strippedName .= substr($branchIdentifier, 0, $this->maxLength - strlen($strippedName) - $hashLength - 1).'-';
        $strippedName .= Hashifier::hash($branchIdentifier, $hashLength);

        return $strippedName;
    }
}
