<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\River\Filter\ContextFactory;
use ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName\EnvironmentNamingStrategy;
use ContinuousPipe\River\Task\Deploy\Naming\UnresolvedEnvironmentNameException;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use Ramsey\Uuid\Uuid;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class ExpressionResolverNamingStrategy implements EnvironmentNamingStrategy
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
    public function getName(Tide $tide, $expression = null)
    {
        if (null === $expression) {
            throw new UnresolvedEnvironmentNameException('The environment name expression must not be blank');
        } elseif (!is_string($expression)) {
            throw new UnresolvedEnvironmentNameException('The environment name expression should be a string');
        }

        $context = $this->contextFactory->create(
            $tide->getFlowUuid(),
            $tide->getCodeReference(),
            $tide
        );

        return $this->resolveExpression($expression, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnvironmentPartOfFlow(Uuid $flowUuid, Environment $environment)
    {
        throw new \RuntimeException('Can\'t let you know that...');
    }

    /**
     * @param $expression
     * @param ArrayObject $context
     *
     * @return string
     *
     * @throws UnresolvedEnvironmentNameException
     */
    private function resolveExpression($expression, ArrayObject $context)
    {
        $language = new ExpressionLanguage();

        try {
            return $language->evaluate($expression, $context->asArray());
        } catch (SyntaxError $e) {
            throw new UnresolvedEnvironmentNameException(sprintf(
                'The expression provided ("%s") is not valid: %s',
                $expression,
                $e->getMessage()
            ), $e->getCode(), $e);
        } catch (\InvalidArgumentException $e) {
            throw new UnresolvedEnvironmentNameException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
