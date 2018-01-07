<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface ChangesComparator
{
    /**
     * List the files that changed between these two references.
     *
     * @param FlatFlow $flow
     * @param string $base
     * @param string $head
     *
     * @throws CodeRepositoryException
     *
     * @return string[]
     */
    public function listChangedFiles(FlatFlow $flow, string $base, string $head) : array;

    /**
     * Returns true if supports the following flow.
     *
     * @param FlatFlow $flow
     *
     * @return bool
     */
    public function supports(FlatFlow $flow) : bool;
}
