<?php

namespace ContinuousPipe\Builder\Reporting;

interface ReportPublisher
{
    /**
     * Publish the given report.
     *
     * @param string $buildIdentifier
     * @param array $report
     *
     * @throws ReportException
     */
    public function publish(string $buildIdentifier, array $report);
}
