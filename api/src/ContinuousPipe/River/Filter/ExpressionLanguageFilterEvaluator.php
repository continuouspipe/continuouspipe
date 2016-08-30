<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

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

        $expression = $filter['expression'];
        $language = new ExpressionLanguage();

        try {
            $evaluated = $language->evaluate($expression, $context->asArray());
        } catch (SyntaxError $e) {
            throw new TideConfigurationException(sprintf(
                'The expression provided ("%s") is not valid: %s',
                $expression,
                $e->getMessage()
            ), $e->getCode(), $e);
        } catch (\InvalidArgumentException $e) {
            throw new TideConfigurationException($e->getMessage(), $e->getCode(), $e);
        } catch (\RuntimeException $e) {
            throw new TideConfigurationException('The filter seems to be wrong, we will investigate', $e->getCode(), $e);
        }

        if (!is_bool($evaluated)) {
            throw new TideConfigurationException(sprintf(
                'Expression "%s" is not valid as it do not return a boolean',
                $expression
            ));
        }

        return $evaluated;
    }
}
