<?php

namespace ContinuousPipe\River\Pipeline\Generation;

use ContinuousPipe\River\Filter\ContextFactory;
use ContinuousPipe\River\Filter\FilterException;
use ContinuousPipe\River\Flow\Configuration;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Pipeline\Pipeline;
use ContinuousPipe\River\Pipeline\PipelineTideGenerator;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use ContinuousPipe\River\TideConfigurationException;
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
     * @var ContextFactory
     */
    private $contextFactory;

    public function __construct(
        TideConfigurationFactory $tideConfigurationFactory,
        TideFactory $tideFactory,
        LoggerInterface $logger,
        ContextFactory $contextFactory
    ) {
        $this->tideConfigurationFactory = $tideConfigurationFactory;
        $this->tideFactory = $tideFactory;
        $this->logger = $logger;
        $this->contextFactory = $contextFactory;
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

        $request = $request->withContinuousPipeExists(
            $configuration->isContinuousPipeFileExists()
        );

        $pipelines = $this->getPipelines($request->getFlow(), $configuration);
        if (empty($pipelines)) {
            $this->logger->warning('No pipeline found in configuration', [
                'flow_uuid' => $request->getFlow()->getUuid(),
            ]);
        }

        foreach ($pipelines as $pipeline) {
            try {
                $matchesCondition = $pipeline->matchesCondition(
                    $this->contextFactory,
                    $request->getFlow()->getUuid(),
                    $request->getCodeReference()
                );
            } catch (FilterException $e) {
                throw new TideConfigurationException($e->getMessage(), $e->getCode(), $e);
            }

            if (!$matchesCondition) {
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
     * @param FlatFlow      $flow
     * @param Configuration $configuration
     *
     * @return Pipeline[]
     */
    private function getPipelines(FlatFlow $flow, Configuration $configuration) : array
    {
        $arrayConfiguration = $configuration->getConfiguration();

        return array_map(function (array $pipelineConfiguration) use ($flow) {
            return Pipeline::withConfiguration($flow, $pipelineConfiguration);
        }, $arrayConfiguration['pipelines']);
    }
}
