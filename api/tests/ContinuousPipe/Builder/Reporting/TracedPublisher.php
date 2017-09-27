<?php

namespace ContinuousPipe\Builder\Reporting;

class TracedPublisher implements ReportPublisher
{
    /**
     * @var ReportPublisher
     */
    private $decoratedPublisher;

    /**
     * @var array
     */
    private $publishedReports = [];

    /**
     * @param ReportPublisher $decoratedPublisher
     */
    public function __construct(ReportPublisher $decoratedPublisher)
    {
        $this->decoratedPublisher = $decoratedPublisher;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(string $buildIdentifier, array $report)
    {
        $this->decoratedPublisher->publish($buildIdentifier, $report);

        $this->publishedReports[] = $report;
    }

    /**
     * @return array
     */
    public function getPublishedReports(): array
    {
        return $this->publishedReports;
    }
}
