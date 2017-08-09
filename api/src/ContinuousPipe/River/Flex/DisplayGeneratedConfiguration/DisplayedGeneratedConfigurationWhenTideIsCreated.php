<?php

namespace ContinuousPipe\River\Flex\DisplayGeneratedConfiguration;

use ContinuousPipe\Flex\ConfigurationGeneration\GeneratedFile;
use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\View\TideRepository;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Complex;
use LogStream\Node\Text;

class DisplayedGeneratedConfigurationWhenTideIsCreated
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var RecordedConfigurationGeneration
     */
    private $recordedConfigurationGeneration;

    public function __construct(TideRepository $tideRepository, LoggerFactory $loggerFactory, RecordedConfigurationGeneration $recordedConfigurationGeneration)
    {
        $this->tideRepository = $tideRepository;
        $this->loggerFactory = $loggerFactory;
        $this->recordedConfigurationGeneration = $recordedConfigurationGeneration;
    }

    public function notify(TideCreated $event)
    {
        if (null === ($configuration = $this->recordedConfigurationGeneration->getLastGeneratedConfiguration())) {
            return;
        }

        $generatedFiles = $configuration->getGeneratedFiles();
        if (count($generatedFiles) === 0) {
            return;
        }

        $generationIsSuccessful = array_reduce($configuration->getGeneratedFiles(), function (bool $carry, GeneratedFile $item) {
            return $carry && !$item->hasFailed();
        }, true);

        $tide = $this->tideRepository->find($event->getTideUuid());
        $logger = $this->loggerFactory->fromId($tide->getLogId());
        $logger
            ->child(new Text('')) // Empty title so we do not display it, we go directly to level 2
            ->child(new Text('Generating configuration'))
            ->updateStatus($generationIsSuccessful ? Log::SUCCESS : Log::FAILURE)
            ->child(new Complex('tabs', [
                'tabs' => array_map(function (GeneratedFile $generatedFile) {
                    return [
                        'name' => $generatedFile->getPath(),
                        'status' => $generatedFile->hasFailed() ? Log::FAILURE : Log::SUCCESS,
                        'contents' => [
                            'type' => $generatedFile->hasFailed() ? 'text' : 'raw',
                            'contents' => $generatedFile->hasFailed() ? $generatedFile->getFailureReason() : $generatedFile->getContents(),
                        ],
                    ];
                }, $generatedFiles)
            ]))
        ;
    }
}
