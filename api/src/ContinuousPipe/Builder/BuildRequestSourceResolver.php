<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\Archive;
use ContinuousPipe\River\CodeReference;

interface BuildRequestSourceResolver
{
    /**
     * @param CodeReference $codeReference
     *
     * @throws BuilderException
     *
     * @return Archive|Repository
     */
    public function getSource(CodeReference $codeReference);
}
