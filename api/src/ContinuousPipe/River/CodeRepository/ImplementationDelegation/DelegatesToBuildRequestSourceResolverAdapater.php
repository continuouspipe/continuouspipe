<?php

namespace ContinuousPipe\River\CodeRepository\ImplementationDelegation;

use ContinuousPipe\Builder\BuilderException;
use ContinuousPipe\River\CodeReference;
use Ramsey\Uuid\UuidInterface;

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
    public function getSource(UuidInterface $flowUuid, CodeReference $codeReference)
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($flowUuid, $codeReference)) {
                return $adapter->getSource($flowUuid, $codeReference);
            }
        }

        throw new BuilderException('No adapater supports the given code reference');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UuidInterface $flowUuid, CodeReference $codeReference): bool
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($flowUuid, $codeReference)) {
                return true;
            }
        }

        return false;
    }
}
