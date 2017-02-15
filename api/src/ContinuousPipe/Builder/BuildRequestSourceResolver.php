<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\Archive;
use ContinuousPipe\River\CodeReference;
use Ramsey\Uuid\UuidInterface;

interface BuildRequestSourceResolver
{
    /**
     * @param CodeReference $codeReference
     *
     * @throws BuilderException
     *
     * @return Archive|Repository
     */
    public function getSource(UuidInterface $flowUuid, CodeReference $codeReference);
}
