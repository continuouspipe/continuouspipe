<?php

namespace ContinuousPipe\River\Task\Deploy\Naming;

use ContinuousPipe\Model\Environment;
use Ramsey\Uuid\Uuid;

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
    public function getName(Uuid $flowUuid, $expression = null)
    {
        $name = $this->namingStrategy->getName($flowUuid, $expression);
        if (strlen($name) <= $this->maxLength) {
            return $name;
        }

        $flowUuidLength = strlen((string) $flowUuid);
        $strippedName = substr($name, 0, $flowUuidLength + 1);
        $branchIdentifier = substr($name, strlen($strippedName));

        $hashLength = 10;
        $strippedName .= substr($branchIdentifier, 0, $this->maxLength - strlen($strippedName) - $hashLength - 1).'-';
        $strippedName .= substr(md5($branchIdentifier), 0, $hashLength);

        return $strippedName;
    }
}
