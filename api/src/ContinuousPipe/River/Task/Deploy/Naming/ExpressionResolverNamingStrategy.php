<?php

namespace ContinuousPipe\River\Task\Deploy\Naming;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\River\Filter\ContextFactory;
use ContinuousPipe\River\Repository\TideRepository;
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
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param ContextFactory $contextFactory
     * @param TideRepository $tideRepository
     */
    public function __construct(ContextFactory $contextFactory, TideRepository $tideRepository)
    {
        $this->contextFactory = $contextFactory;
        $this->tideRepository = $tideRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(Uuid $tideUuid, $expression = null)
    {
        if (null === $expression) {
            throw new UnresolvedEnvironmentNameException('The environment name expression must not be blank');
        } elseif (!is_string($expression)) {
            throw new UnresolvedEnvironmentNameException('The environment name expression should be a string');
        }

        $tide = $this->tideRepository->find($tideUuid);
        $context = $this->contextFactory->create($tide);

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
