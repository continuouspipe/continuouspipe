<?php

namespace ContinuousPipe\River\Filter;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

final class Filter
{
    private $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * Evaluates the filter with the given context.
     *
     * @param array $context
     *
     * @throws FilterException
     *
     * @return bool
     */
    public function evaluates(array $context) : bool
    {
        $language = new ExpressionLanguage();

        try {
            $evaluated = $language->evaluate($this->expression, $context);
        } catch (SyntaxError $e) {
            throw new FilterException(sprintf(
                'The expression provided ("%s") is not valid: %s',
                $this->expression,
                $e->getMessage()
            ), $e->getCode(), $e);
        } catch (\InvalidArgumentException $e) {
            throw new FilterException($e->getMessage(), $e->getCode(), $e);
        } catch (\RuntimeException $e) {
            throw new FilterException('The filter seems to be wrong, we will investigate', $e->getCode(), $e);
        }

        if (!is_bool($evaluated)) {
            throw new FilterException(sprintf(
                'Expression "%s" is not valid as it does not return a boolean',
                $this->expression
            ));
        }

        return $evaluated;
    }
}
