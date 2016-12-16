<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;

class ExpressionLanguageFilterEvaluator implements FilterEvaluator
{
    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @param ContextFactory $contextFactory
     */
    public function __construct(ContextFactory $contextFactory)
    {
        $this->contextFactory = $contextFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluates(Tide $tide, array $filter)
    {
        $context = $this->contextFactory->create($tide);

        try {
            return (new Filter($filter['expression']))->evaluates($context->asArray());
        } catch (FilterException $e) {
            throw new TideConfigurationException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
