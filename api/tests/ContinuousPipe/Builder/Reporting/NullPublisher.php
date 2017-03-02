<?php

namespace ContinuousPipe\Builder\Reporting;

class NullPublisher implements ReportPublisher
{
    /**
     * {@inheritdoc}
     */
    public function publish(string $buildIdentifier, array $report)
    {
    }
}
