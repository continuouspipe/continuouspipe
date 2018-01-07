<?php

namespace ContinuousPipe\River\Pipeline;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Filter\CodeChanges\CodeChangesResolver;
use ContinuousPipe\River\Filter\ContextFactory;
use ContinuousPipe\River\Filter\Filter;
use ContinuousPipe\River\Filter\FilterException;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Pipeline
{
    const CONTINUOUS_PIPE_NAMESPACE = 'c573e09e-274e-4576-98b9-3df9ec90b687';

    private $uuid;
    private $configuration;

    private function __construct(UuidInterface $uuid, array $configuration)
    {
        $this->uuid = $uuid;
        $this->configuration = $configuration;
    }

    /**
     * @param FlatFlow $flow
     * @param array    $pipelineConfiguration
     *
     * @return Pipeline
     */
    public static function withConfiguration(FlatFlow $flow, array $pipelineConfiguration)
    {
        $pipeline = 'io.continuouspipe.flows.'.$flow->getUuid()->toString().'.'.$pipelineConfiguration['name'];

        return new self(
            Uuid::uuid5(self::CONTINUOUS_PIPE_NAMESPACE, $pipeline),
            $pipelineConfiguration
        );
    }

    /**
     * @param CodeChangesResolver $codeChangesResolver
     * @param ContextFactory $contextFactory
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     *
     * @throws FilterException
     *
     * @return bool
     */
    public function matchesCondition(CodeChangesResolver $codeChangesResolver, ContextFactory $contextFactory, UuidInterface $flowUuid, CodeReference $codeReference) : bool
    {
        if (!isset($this->configuration['condition'])) {
            return true;
        }

        $filter = new Filter(
            $codeChangesResolver,
            $flowUuid,
            $codeReference,
            $contextFactory->create($flowUuid, $codeReference)->asArray()
        );

        return $filter->evaluates($this->configuration['condition']);
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getName() : string
    {
        return $this->configuration['name'];
    }

    public function getConfiguration() : array
    {
        return $this->configuration;
    }
}
