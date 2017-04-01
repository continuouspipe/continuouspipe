<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\Filter\CodeChanges\CodeChangesResolver;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;

class ExpressionLanguageFilterEvaluator implements FilterEvaluator
{
    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @var CodeChangesResolver
     */
    private $codeChangesResolver;

    /**
     * @param ContextFactory $contextFactory
     * @param CodeChangesResolver $codeChangesResolver
     */
    public function __construct(ContextFactory $contextFactory, CodeChangesResolver $codeChangesResolver)
    {
        $this->contextFactory = $contextFactory;
        $this->codeChangesResolver = $codeChangesResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluates(Tide $tide, array $filter)
    {
        $context = $this->contextFactory->create(
            $tide->getFlowUuid(),
            $tide->getCodeReference(),
            $tide
        );

        try {
            return Filter::forTide($this->codeChangesResolver, $tide, $context->asArray())->evaluates($filter['expression']);
        } catch (FilterException $e) {
            throw new TideConfigurationException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
