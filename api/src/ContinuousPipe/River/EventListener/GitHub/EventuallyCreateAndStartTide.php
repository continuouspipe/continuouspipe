<?php

namespace ContinuousPipe\River\EventListener\GitHub;

use ContinuousPipe\River\Event\CodeRepositoryEvent;
use ContinuousPipe\River\Filter\ContextFactory;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Tide\StartVoter\TideStartVoter;
use ContinuousPipe\River\TideConfigurationException;
use ContinuousPipe\River\TideFactory;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class EventuallyCreateAndStartTide
{
    /**
     * @var TideFactory
     */
    private $tideFactory;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var TideStartVoter
     */
    private $tideStartVoter;

    /**
     * @var ContextFactory
     */
    private $contextFactory;
    /**
     * @var FlatFlowRepository
     */
    private $flatFlowRepository;

    /**
     * @param TideFactory        $tideFactory
     * @param MessageBus         $eventBus
     * @param ContextFactory     $contextFactory
     * @param LoggerFactory      $loggerFactory
     * @param TideStartVoter     $tideStartVoter
     * @param FlatFlowRepository $flatFlowRepository
     */
    public function __construct(TideFactory $tideFactory, MessageBus $eventBus, ContextFactory $contextFactory, LoggerFactory $loggerFactory, TideStartVoter $tideStartVoter, FlatFlowRepository $flatFlowRepository)
    {
        $this->tideFactory = $tideFactory;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
        $this->tideStartVoter = $tideStartVoter;
        $this->contextFactory = $contextFactory;
        $this->flatFlowRepository = $flatFlowRepository;
    }

    /**
     * @param CodeRepositoryEvent $event
     */
    public function notify(CodeRepositoryEvent $event)
    {
        $tide = $this->tideFactory->createFromCodeReference(
            $this->flatFlowRepository->find($event->getFlowUuid()),
            $event->getCodeReference(),
            $event
        );

        try {
            $context = $this->contextFactory->create($tide);

            if (!$this->tideStartVoter->vote($tide, $context)) {
                return;
            }
        } catch (TideConfigurationException $e) {
            $logger = $this->loggerFactory->from($tide->getContext()->getLog());
            $logger->child(new Text('Tide filter error: '.$e->getMessage()));
        }

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }
}
