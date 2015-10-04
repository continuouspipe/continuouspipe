<?php

namespace ContinuousPipe\River\Flow\ConfigurationFinalizer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\TideConfigurationException;
use ContinuousPipe\River\TideConfigurationFactory;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class FilterTasks implements TideConfigurationFactory
{
    /**
     * @var TideConfigurationFactory
     */
    private $factory;

    /**
     * @param TideConfigurationFactory $factory
     */
    public function __construct(TideConfigurationFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(Flow $flow, CodeReference $codeReference)
    {
        $configuration = $this->factory->getConfiguration($flow, $codeReference);
        $context = $this->getTideContext($flow, $codeReference);

        foreach ($configuration['tasks'] as $name => $task) {
            if (!isset($task['filter'])) {
                continue;
            }

            if (!$this->taskMatchFilter($task, $context)) {
                unset($configuration['tasks'][$name]);
            }
        }

        return $configuration;
    }

    /**
     * Evaluate the task against its filter.
     *
     * @param array $task
     * @param array $context
     *
     * @return string
     *
     * @throws TideConfigurationException
     */
    private function taskMatchFilter(array $task, array $context)
    {
        $expression = $task['filter']['expression'];
        $language = new ExpressionLanguage();

        try {
            $evaluated = $language->evaluate($expression, $context);
        } catch (SyntaxError $e) {
            throw new TideConfigurationException(sprintf(
                'The expression provided ("%s") is not valid: %s',
                $expression,
                $e->getMessage()
            ));
        }

        if (!is_bool($evaluated)) {
            throw new TideConfigurationException(sprintf(
                'Expression "%s" is not valid as it do not return a boolean',
                $expression
            ));
        }

        return $evaluated;
    }

    /**
     * Get context against what the filters will be evaluated.
     *
     * @param Flow          $flow
     * @param CodeReference $codeReference
     *
     * @return array
     */
    private function getTideContext(Flow $flow, CodeReference $codeReference)
    {
        return [
            'codeReference' => Flow\FilterContext\CodeReferenceRepresentation::fromCodeReference($codeReference),
        ];
    }
}
