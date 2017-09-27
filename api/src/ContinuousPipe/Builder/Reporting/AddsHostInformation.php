<?php

namespace ContinuousPipe\Builder\Reporting;

class AddsHostInformation implements ReportBuilder
{
    /**
     * @var ReportBuilder
     */
    private $decoratedBuilder;

    /**
     * @param ReportBuilder $decoratedBuilder
     */
    public function __construct(ReportBuilder $decoratedBuilder)
    {
        $this->decoratedBuilder = $decoratedBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $buildIdentifier): array
    {
        $report = $this->decoratedBuilder->build($buildIdentifier);
        $report['host'] = [
            'hostname' => getenv('HOSTNAME') ?: gethostname(),
        ];

        return $report;
    }
}
