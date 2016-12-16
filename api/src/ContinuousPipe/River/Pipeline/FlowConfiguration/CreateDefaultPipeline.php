<?php

namespace ContinuousPipe\River\Pipeline\FlowConfiguration;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\TideConfigurationFactory;

final class CreateDefaultPipeline implements TideConfigurationFactory
{
    private $decoratedFactory;

    public function __construct(TideConfigurationFactory $decoratedFactory)
    {
        $this->decoratedFactory = $decoratedFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(FlatFlow $flow, CodeReference $codeReference)
    {
        $configuration = $this->decoratedFactory->getConfiguration($flow, $codeReference);

        if (empty($configuration['pipelines'])) {
            $configuration['pipelines'] = [
                [
                    'name' => 'Default pipeline',
                    'tasks' => array_map(function ($name) {
                        return [
                            'imports' => $name,
                        ];
                    }, array_keys($configuration['tasks'])),
                ],
            ];
        }

        return $configuration;
    }
}
