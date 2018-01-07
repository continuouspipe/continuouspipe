<?php

namespace ContinuousPipe\River\CodeRepository\ImplementationDelegation;

use ContinuousPipe\River\CodeRepository\ChangesComparator;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class DelegatesToChangesComparator implements ChangesComparator
{
    /**
     * @var array|ChangesComparator[]
     */
    private $comparators;

    /**
     * @param ChangesComparator[] $comparators
     */
    public function __construct(array $comparators)
    {
        $this->comparators = $comparators;
    }

    /**
     * {@inheritdoc}
     */
    public function listChangedFiles(FlatFlow $flow, string $base, string $head): array
    {
        foreach ($this->comparators as $comparator) {
            if ($comparator->supports($flow)) {
                return $comparator->listChangedFiles($flow, $base, $head);
            }
        }

        throw new CodeRepositoryException('Changes comparison is not yet supported for this flow');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        foreach ($this->comparators as $comparator) {
            if ($comparator->supports($flow)) {
                return true;
            }
        }

        return false;
    }
}
