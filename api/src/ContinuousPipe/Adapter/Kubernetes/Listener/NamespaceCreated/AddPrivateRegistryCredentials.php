<?php

namespace ContinuousPipe\Adapter\Kubernetes\Listener\NamespaceCreated;

use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Event\NamespaceCreated;
use ContinuousPipe\Adapter\Kubernetes\PrivateImages\SecretFactory;
use Kubernetes\Client\Model\LocalObjectReference;
use Kubernetes\Client\Model\Secret;
use Kubernetes\Client\Model\ServiceAccount;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class AddPrivateRegistryCredentials
{
    /**
     * @var KubernetesClientFactory
     */
    private $kubernetesClientFactory;

    /**
     * @var SecretFactory
     */
    private $secretFactory;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param KubernetesClientFactory $kubernetesClientFactory
     * @param SecretFactory           $secretFactory
     * @param LoggerFactory           $loggerFactory
     */
    public function __construct(KubernetesClientFactory $kubernetesClientFactory, SecretFactory $secretFactory, LoggerFactory $loggerFactory)
    {
        $this->kubernetesClientFactory = $kubernetesClientFactory;
        $this->secretFactory = $secretFactory;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param NamespaceCreated $event
     */
    public function notify(NamespaceCreated $event)
    {
        $deploymentContext = $event->getContext();
        $logger = $this->loggerFactory->from($deploymentContext->getLog());

        try {
            $secret = $this->secretFactory->createDockerRegistrySecret($deploymentContext);

            $client = $this->kubernetesClientFactory->getByCluster($deploymentContext->getCluster());
            $namespaceClient = $client->getNamespaceClient($event->getNamespace());

            $secret = $namespaceClient->getSecretRepository()->create($secret);

            $serviceAccountRepository = $namespaceClient->getServiceAccountRepository();
            $defaultServiceAccount = $serviceAccountRepository->findByName('default');

            if (!$this->alreadyHaveSecret($defaultServiceAccount, $secret)) {
                $imagePullSecrets = $defaultServiceAccount->getImagePullSecrets();
                $imagePullSecrets[] = new LocalObjectReference(
                    $secret->getMetadata()->getName()
                );

                $serviceAccountRepository->update(new ServiceAccount(
                    $defaultServiceAccount->getMetadata(),
                    $defaultServiceAccount->getSecrets(),
                    $imagePullSecrets
                ));

                $logger->child(new Text('Added the Docker Registry credentials in the environment'));
            }
        } catch (\Exception $e) {
            $logger->child(new Text('WARNING - Unable to add namespace credentials: '.$e->getMessage().' ('.get_class($e).')'));

            throw $e;
        }
    }

    /**
     * @param ServiceAccount $serviceAccount
     * @param Secret         $secret
     *
     * @return bool
     */
    private function alreadyHaveSecret(ServiceAccount $serviceAccount, Secret $secret)
    {
        foreach ($serviceAccount->getImagePullSecrets() as $reference) {
            if ($reference->getName() == $secret->getMetadata()->getName()) {
                return true;
            }
        }

        return false;
    }
}
