<?php

namespace ContinuousPipe\River\Managed\Resources\History\Handler;

use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Managed\Resources\History\Command\GetAndStoreResourceUsageCommand;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistoryEntry;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistoryEntryRepository;
use ContinuousPipe\River\Managed\Resources\ResourceUsageResolver;
use Psr\Log\LoggerInterface;

class GetAndStoreResourceUsage
{
    /**
     * @var ResourceUsageResolver
     */
    private $resourceUsageResolver;
    /**
     * @var ResourceUsageHistoryEntryRepository
     */
    private $historyEntryRepository;
    /**
     * @var FlatFlowRepository
     */
    private $flatFlowRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ResourceUsageResolver $resourceUsageResolver,
        ResourceUsageHistoryEntryRepository $historyEntryRepository,
        FlatFlowRepository $flatFlowRepository,
        LoggerInterface $logger
    ) {
        $this->resourceUsageResolver = $resourceUsageResolver;
        $this->historyEntryRepository = $historyEntryRepository;
        $this->flatFlowRepository = $flatFlowRepository;
        $this->logger = $logger;
    }

    public function handle(GetAndStoreResourceUsageCommand $command)
    {
        try {
            $flow = $this->flatFlowRepository->find($command->getFlowUuid());

            $this->historyEntryRepository->save(new ResourceUsageHistoryEntry(
                $command->getFlowUuid(),
                new \DateTime(),
                $this->resourceUsageResolver->forFlow($flow)
            ));
        } catch (\Exception $e) {
            $this->logger->error('Cannot get and store flow resources', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
        }
    }
}
