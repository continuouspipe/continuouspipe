<?php

namespace ContinuousPipe\Adapter\Kubernetes\Listener\NamespaceCreated;

use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Event\NamespaceCreated;
use ContinuousPipe\Adapter\Kubernetes\PrivateImages\SecretFactory;
use Kubernetes\Client\Model\LocalObjectReference;
use Kubernetes\Client\Model\Secret;
use Kubernetes\Client\Model\ServiceAccount;
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
     * @param KubernetesClientFactory $kubernetesClientFactory
     * @param SecretFactory           $secretFactory
     */
    public function __construct(KubernetesClientFactory $kubernetesClientFactory, SecretFactory $secretFactory)
    {
        $this->kubernetesClientFactory = $kubernetesClientFactory;
        $this->secretFactory = $secretFactory;
    }

    /**
     * @param NamespaceCreated $event
     */
    public function notify(NamespaceCreated $event)
    {
        $deploymentContext = $event->getContext();
        $logger = $deploymentContext->getLogger();

        try {
            $secret = $this->secretFactory->createDockerRegistrySecret($deploymentContext);

            $client = $this->kubernetesClientFactory->getByProvider($deploymentContext->getProvider());
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

                $logger->append(new Text('Added the Docker Registry credentials in the environment'));
            }
        } catch (\Exception $e) {
            $logger->append(new Text('WARNING - Unable to add namespace credentials: '.$e->getMessage().' ('.get_class($e).')'));

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
