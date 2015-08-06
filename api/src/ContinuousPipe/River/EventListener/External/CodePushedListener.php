<?php

namespace ContinuousPipe\River\EventListener\External;

use ContinuousPipe\River\Event\External\CodePushedEvent;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\TideFactory;
use LogStream\LoggerFactory;

class CodePushedListener
{
    /**
     * @var FlowRepository
     */
    private $flowRepository;
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;
    /**
     * @var TideFactory
     */
    private $tideFactory;

    /**
     * @param FlowRepository $flowRepository
     * @param LoggerFactory  $loggerFactory
     * @param TideFactory    $tideFactory
     */
    public function __construct(FlowRepository $flowRepository, LoggerFactory $loggerFactory, TideFactory $tideFactory)
    {
        $this->flowRepository = $flowRepository;
        $this->loggerFactory = $loggerFactory;
        $this->tideFactory = $tideFactory;
    }

    /**
     * @param CodePushedEvent $event
     */
    public function notify(CodePushedEvent $event)
    {
        $repository = $event->getRepository();
        $flow = $this->flowRepository->findOneByRepositoryIdentifier($repository->getIdentifier());

        $logger = $this->loggerFactory->create();

        $this->tideFactory->create($event->getUuid(), $flow, $event->getCodeReference(), $logger->getLog());
    }
}
