<?php

namespace ContinuousPipe\River\Pipeline\Generation;

use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Pipeline\Pipeline;
use ContinuousPipe\River\Pipeline\PipelineTideGenerator;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use ContinuousPipe\River\TideConfigurationFactory;
use ContinuousPipe\River\TideFactory;
use Psr\Log\LoggerInterface;

class TideForEachPipelineGenerator implements PipelineTideGenerator
{
    /**
     * @var TideConfigurationFactory
     */
    private $tideConfigurationFactory;

    /**
     * @var TideFactory
     */
    private $tideFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TideConfigurationFactory $tideConfigurationFactory
     * @param TideFactory              $tideFactory
     * @param LoggerInterface          $logger
     */
    public function __construct(TideConfigurationFactory $tideConfigurationFactory, TideFactory $tideFactory, LoggerInterface $logger)
    {
        $this->tideConfigurationFactory = $tideConfigurationFactory;
        $this->tideFactory = $tideFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(TideGenerationRequest $request): array
    {
        $tides = [];
        $configuration = $this->tideConfigurationFactory->getConfiguration(
            $request->getFlow(),
            $request->getCodeReference()
        );

        $pipelines = $this->getPipelines($request->getFlow(), $configuration);
        if (empty($pipelines)) {
            $this->logger->warning('No pipeline found in configuration', [
                'flow_uuid' => $request->getFlow()->getUuid(),
            ]);
        }

        foreach ($pipelines as $pipeline) {
            if (!$pipeline->matchesCondition($request->getCodeReference())) {
                $this->logger->debug('Pipeline condition not matched, skipping it', [
                    'flow_uuid' => $request->getFlow()->getUuid(),
                    'pipeline' => $pipeline->getName(),
                ]);

                continue;
            }

            $tides[] = $this->tideFactory->create($pipeline, $request);
        }

        return $tides;
    }

    /**
     * @param FlatFlow $flow
     * @param array    $configuration
     *
     * @return Pipeline[]
     */
    private function getPipelines(FlatFlow $flow, array $configuration) : array
    {
        return array_map(function (array $pipelineConfiguration) use ($flow) {
            return Pipeline::withConfiguration($flow, $pipelineConfiguration);
        }, $configuration['pipelines']);
    }
}
