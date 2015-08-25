<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use ContinuousPipe\Adapter\Kubernetes\Cluster;
use ContinuousPipe\Adapter\Kubernetes\KubernetesProvider;
use ContinuousPipe\Adapter\Kubernetes\User;
use ContinuousPipe\Adapter\Provider;
use ContinuousPipe\Adapter\ProviderRepository;

class ProviderContext implements Context
{
    const DEFAULT_PROVIDER_NAME = 'testing';

    /**
     * @var ProviderRepository
     */
    private $providerRepository;

    /**
     * @param ProviderRepository $providerRepository
     */
    public function __construct(ProviderRepository $providerRepository)
    {
        $this->providerRepository = $providerRepository;
    }

    /**
     * @Given I have a valid Kubernetes provider
     */
    public function iHaveAValidKubernetesProvider()
    {
        return $this->providerRepository->create(new KubernetesProvider(
            self::DEFAULT_PROVIDER_NAME,
            new Cluster('1.2.3.4', 'v1'),
            new User('username', 'password')
        ));
    }

    /**
     * @Then the Kubernetes cloud provider must be successfully saved
     */
    public function theKubernetesCloudProviderMustBeSuccessfullySaved()
    {
        $kubernetesProviders = array_filter($this->providerRepository->findAll(), function (Provider $provider) {
            return $provider instanceof KubernetesProvider;
        });

        if (0 === count($kubernetesProviders)) {
            throw new \RuntimeException(sprintf(
                'Expected kubernetes providers but found 0'
            ));
        }
    }
}
