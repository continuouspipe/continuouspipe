<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\TideConfigurationFactory;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Yaml;

class TideConfigurationContext implements Context
{
    /**
     * @var TideConfigurationFactory
     */
    private $tideConfigurationFactory;

    /**
     * @var \FlowContext
     */
    private $flowContext;

    /**
     * @var array|null
     */
    private $configuration;

    /**
     * @param TideConfigurationFactory $tideConfigurationFactory
     */
    public function __construct(TideConfigurationFactory $tideConfigurationFactory)
    {
        $this->tideConfigurationFactory = $tideConfigurationFactory;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->flowContext = $scope->getEnvironment()->getContext('FlowContext');
    }

    /**
     * @When the configuration of the tide is generated
     */
    public function theConfigurationOfTheTideIsGenerated()
    {
        $flow = $this->flowContext->createFlow();
        $this->configuration = $this->tideConfigurationFactory->getConfiguration($flow, new CodeReference(
            $flow->getContext()->getCodeRepository(),
            'sha1'
        ));
    }

    /**
     * @Then the generated configuration should contain at least:
     */
    public function theGeneratedConfigurationShouldContainAtLeast(PyStringNode $string)
    {
        $expectedConfiguration = Yaml::parse($string->getRaw());
        $intersection = $this->array_intersect_recursive($expectedConfiguration, $this->configuration);

        if ($intersection != $expectedConfiguration) {
            throw new \RuntimeException(sprintf(
                'Expected to have at least this configuration but found: %s',
                PHP_EOL.Yaml::dump($this->configuration)
            ));
        }
    }

    /**
     * @Then the generated configuration should not contain:
     */
    public function theGeneratedConfigurationShouldNotContain(PyStringNode $string)
    {
        $expectedConfiguration = Yaml::parse($string->getRaw());
        $intersection = $this->array_intersect_recursive($expectedConfiguration, $this->configuration);

        if ($intersection == $expectedConfiguration) {
            throw new \RuntimeException(sprintf(
                'Expected to NOT have this configuration but found: %s',
                PHP_EOL.Yaml::dump($this->configuration)
            ));
        }
    }

    /**
     * @Then the generated configuration should not contain the path :path
     */
    public function theGeneratedConfigurationShouldNotContainThePath($path)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if ($propertyAccessor->isReadable($this->configuration, $path)) {
            $value = $propertyAccessor->getValue($this->configuration, $path);

            if (null !== $value) {
                throw new \RuntimeException(sprintf(
                    'The path "%s" is readable in the configuration and its value is not null',
                    $path
                ));
            }
        }
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public function array_intersect_recursive($array1, $array2)
    {
        foreach($array1 as $key => $value)
        {
            if (!isset($array2[$key]))
            {
                unset($array1[$key]);
            }
            else
            {
                if (is_array($array1[$key]))
                {
                    $array1[$key] = $this->array_intersect_recursive($array1[$key], $array2[$key]);
                }
                elseif ($array2[$key] !== $value)
                {
                    unset($array1[$key]);
                }
            }
        }
        return $array1;
    }
}