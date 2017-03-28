<?php

namespace ContinuousPipe\River\Flow\Variable;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\River\TideConfigurationException;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class FlowVariableResolver
{
    public function createContext(UuidInterface $flowUuid, CodeReference $codeReference) : ArrayObject
    {
        return new ArrayObject([
            'code_reference' => new ArrayObject([
                'branch' => $codeReference->getBranch(),
                'sha' => $codeReference->getCommitSha(),
            ]),
            'flow' => new ArrayObject([
                'uuid' => $flowUuid->toString(),
            ]),
        ]);
    }

    /**
     * @param string $expression
     * @param ArrayObject $context
     *
     * @return mixed
     *
     * @throws TideConfigurationException
     */
    public function resolveExpression(string $expression, ArrayObject $context)
    {
        $language = new ExpressionLanguage();

        try {
            return $language->evaluate($expression, $context->asArray());
        } catch (SyntaxError $e) {
            throw new TideConfigurationException(sprintf(
                'The expression provided ("%s") is not valid: %s',
                $expression,
                $e->getMessage()
            ), $e->getCode(), $e);
        } catch (\InvalidArgumentException $e) {
            throw new TideConfigurationException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
