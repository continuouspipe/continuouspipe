<?php

namespace ContinuousPipe\River\Flow\ConfigurationFinalizer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\River\TideConfigurationException;
use ContinuousPipe\River\TideConfigurationFactory;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class ReplaceEnvironmentVariableValues implements TideConfigurationFactory
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

        $variables = $this->resolveVariables($configuration, $this->createContext($flow, $codeReference));

        return self::replaceValues($configuration, $variables);
    }

    /**
     * @param array       $configuration
     * @param ArrayObject $context
     *
     * @return array
     */
    private function resolveVariables(array $configuration, ArrayObject $context)
    {
        $variables = [];
        foreach ($configuration['environment_variables'] as $item) {
            if ($this->shouldResolveVariable($item, $context)) {
                $variables[$item['name']] = $item['value'];
            }
        }

        return $variables;
    }

    /**
     * @param array $array
     * @param array $mapping
     *
     * @return array
     */
    public static function replaceValues(array $array, array $mapping)
    {
        $variableKeys = array_map(function ($key) {
            return sprintf('${%s}', $key);
        }, array_keys($mapping));

        array_walk_recursive($array, function (&$value) use ($variableKeys, $mapping) {
            if (is_string($value)) {
                $value = str_replace($variableKeys, array_values($mapping), $value);
            }
        });

        return $array;
    }

    /**
     * @param Flow          $flow
     * @param CodeReference $codeReference
     *
     * @return ArrayObject
     */
    private function createContext(Flow $flow, CodeReference $codeReference)
    {
        return new ArrayObject([
            'code_reference' => new ArrayObject([
                'branch' => $codeReference->getBranch(),
                'sha' => $codeReference->getCommitSha(),
            ]),
            'flow' => new ArrayObject([
                'uuid' => (string) $flow->getUuid(),
            ]),
        ]);
    }

    /**
     * @param array       $item
     * @param ArrayObject $context
     *
     * @return bool
     *
     * @throws TideConfigurationException
     */
    private function shouldResolveVariable(array $item, ArrayObject $context)
    {
        if (!array_key_exists('condition', $item)) {
            return true;
        }

        return $this->isConditionValid($item['condition'], $context);
    }

    /**
     * @param string      $expression
     * @param ArrayObject $context
     *
     * @return bool
     *
     * @throws TideConfigurationException
     */
    private function isConditionValid($expression, ArrayObject $context)
    {
        $language = new ExpressionLanguage();

        try {
            return (bool) $language->evaluate($expression, $context->asArray());
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
