<?php

namespace ContinuousPipe\Builder\Reporting;

interface ReportBuilder
{
    /**
     * @param string $buildIdentifier
     *
     * @throws ReportException
     *
     * @return array
     */
    public function build(string $buildIdentifier) : array;
}
