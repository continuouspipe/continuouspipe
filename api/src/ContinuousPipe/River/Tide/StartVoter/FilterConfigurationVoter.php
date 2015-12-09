<?php

namespace ContinuousPipe\River\Tide\StartVoter;

use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class FilterConfigurationVoter implements TideStartVoter
{
    /**
     * {@inheritdoc}
     */
    public function vote(Tide $tide, Tide\Configuration\ArrayObject $context)
    {
        $configuration = $tide->getContext()->getConfiguration();
        if (!array_key_exists('filter', $configuration)) {
            return true;
        }

        $language = new ExpressionLanguage();
        try {
            return $language->evaluate($configuration['filter'], $context->asArray());
        } catch (SyntaxError $e) {
            throw new TideConfigurationException($e->getMessage(), $e->getCode(), $e);
        } catch (\InvalidArgumentException $e) {
            throw new TideConfigurationException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
