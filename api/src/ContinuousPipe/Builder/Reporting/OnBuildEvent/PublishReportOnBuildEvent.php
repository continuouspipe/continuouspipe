<?php


namespace ContinuousPipe\Builder\Reporting\OnBuildEvent;

use ContinuousPipe\Builder\Aggregate\Event\BuildEvent;
use ContinuousPipe\Builder\Reporting\ReportBuilder;
use ContinuousPipe\Builder\Reporting\ReportException;
use ContinuousPipe\Builder\Reporting\ReportPublisher;
use Psr\Log\LoggerInterface;

class PublishReportOnBuildEvent
{
    /**
     * @var ReportBuilder
     */
    private $reportBuilder;

    /**
     * @var ReportPublisher
     */
    private $reportPublisher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ReportBuilder $reportBuilder, ReportPublisher $reportPublisher, LoggerInterface $logger)
    {
        $this->reportBuilder = $reportBuilder;
        $this->reportPublisher = $reportPublisher;
        $this->logger = $logger;
    }

    public function notify(BuildEvent $event)
    {
        try {
            $this->reportPublisher->publish(
                $event->getBuildIdentifier(),
                $this->reportBuilder->build($event->getBuildIdentifier())
            );
        } catch (ReportException $e) {
            $this->logger->warning('Unable to publish the build report', [
                'exception' => $e,
            ]);
        }
    }
}
