<?php

namespace ContinuousPipe\River\Pipeline;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Filter\Filter;
use ContinuousPipe\River\Filter\FilterException;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;

final class Pipeline
{
    private $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param CodeReference $codeReference
     *
     * @throws FilterException
     *
     * @return bool
     */
    public function matchesCondition(CodeReference $codeReference) : bool
    {
        if (!isset($this->configuration['condition'])) {
            return true;
        }

        return (new Filter($this->configuration['condition']))->evaluates([
            'code_reference' => new ArrayObject([
                'branch' => $codeReference->getBranch(),
                'sha1' => $codeReference->getCommitSha(),
            ]),
        ]);
    }

    public function getName() : string
    {
        return $this->configuration['name'];
    }

    /**
     * @return array
     */
    public function getConfiguration() : array
    {
        return $this->configuration;
    }
}
