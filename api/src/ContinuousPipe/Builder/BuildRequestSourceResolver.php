<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\ArchiveSource;
use ContinuousPipe\River\CodeReference;
use Ramsey\Uuid\UuidInterface;

interface BuildRequestSourceResolver
{
    /**
     * @param CodeReference $codeReference
     *
     * @throws BuilderException
     *
     * @return ArchiveSource|Repository
     */
    public function getSource(UuidInterface $flowUuid, CodeReference $codeReference);
}
