<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace\TraceableNamespaceRepository;
use Kubernetes\Client\Exception\NamespaceNotFound;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\ObjectMetadata;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class NamespaceContext implements Context, SnippetAcceptingContext
{
    /**
     * @var \EnvironmentContext
     */
    private $environmentContext;

    /**
     * @var TraceableNamespaceRepository
     */
    private $namespaceRepository;

    /**
     * @param TraceableNamespaceRepository $namespaceRepository
     */
    public function __construct(TraceableNamespaceRepository $namespaceRepository)
    {
        $this->namespaceRepository = $namespaceRepository;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->environmentContext = $scope->getEnvironment()->getContext('EnvironmentContext');
    }

    /**
     * @When I send a deployment request for a non-existing environment
     */
    public function iSendADeploymentRequestForANonExistingEnvironment()
    {
        $this->environmentContext->sendDeploymentRequest('kubernetes/'.ProviderContext::DEFAULT_PROVIDER_NAME, 'non-existing');
    }

    /**
     * @When I send a deployment request from application template :template
     */
    public function iSendADeploymentRequestFromApplicationTemplate($template)
    {
        $this->iHaveANamespace('existing');
        $this->environmentContext->sendDeploymentRequest('kubernetes/'.ProviderContext::DEFAULT_PROVIDER_NAME, 'existing', $template);
    }

    /**
     * @Then it should create a new namespace
     */
    public function itShouldCreateANewNamespace()
    {
        $numberOfCreatedNamespaces = count($this->namespaceRepository->getCreatedRepositories());

        if ($numberOfCreatedNamespaces == 0) {
            throw new \RuntimeException('No namespace were created');
        }
    }

    /**
     * @Given I have a namespace :name
     */
    public function iHaveANamespace($name)
    {
        try {
            $this->namespaceRepository->findOneByName($name);
        } catch (NamespaceNotFound $e) {
            $this->namespaceRepository->create(new KubernetesNamespace(new ObjectMetadata($name)));
            $this->namespaceRepository->clear();
        }
    }

    /**
     * @When I send a deployment request for the environment :environmentName
     */
    public function iSendADeploymentRequestForTheEnvironment($environmentName)
    {
        $this->environmentContext->sendDeploymentRequest('kubernetes/'.ProviderContext::DEFAULT_PROVIDER_NAME, $environmentName);
    }

    /**
     * @Then it should reuse this namespace
     */
    public function itShouldReuseThisNamespace()
    {
        $numberOfCreatedNamespaces = count($this->namespaceRepository->getCreatedRepositories());

        if ($numberOfCreatedNamespaces !== 0) {
            throw new \RuntimeException(sprintf(
                'Expected 0 namespace to be created, got %d',
                $numberOfCreatedNamespaces
            ));
        }
    }
}
