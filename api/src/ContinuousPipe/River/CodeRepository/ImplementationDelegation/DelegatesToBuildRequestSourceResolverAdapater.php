<?php

namespace ContinuousPipe\River\CodeRepository\ImplementationDelegation;

use ContinuousPipe\Builder\BuilderException;
use ContinuousPipe\River\CodeReference;

class DelegatesToBuildRequestSourceResolverAdapater implements BuildRequestSourceResolverAdapter
{
    /**
     * @var array|BuildRequestSourceResolverAdapter[]
     */
    private $adapters;

    /**
     * @param BuildRequestSourceResolverAdapter[] $adapters
     */
    public function __construct(array $adapters)
    {
        $this->adapters = $adapters;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource(CodeReference $codeReference)
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($codeReference)) {
                return $adapter->getSource($codeReference);
            }
        }

        throw new BuilderException('No adapater supports the given code reference');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CodeReference $codeReference): bool
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($codeReference)) {
                return true;
            }
        }

        return false;
    }
}
