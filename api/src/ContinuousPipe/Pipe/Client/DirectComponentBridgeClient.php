<?php

namespace ContinuousPipe\Pipe\Client;

use ContinuousPipe\Pipe\Command\StartDeploymentCommand;
use ContinuousPipe\Pipe\DeploymentRequest\Target;
use ContinuousPipe\Pipe\EnvironmentClientFactory;
use ContinuousPipe\Pipe\EnvironmentNotFound;
use ContinuousPipe\Pipe\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Pipe\PodNotFound;
use ContinuousPipe\Pipe\View\DeploymentRepository;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use function GuzzleHttp\Promise\promise_for;
use Kubernetes\Client\Exception\ServerError;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\ObjectMetadata;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ContinuousPipe\Pipe\DeploymentRequest as PipeDeploymentRequest;
use ContinuousPipe\Pipe\View\Deployment as PipeViewDeployment;

/**
 * After merging the previously independent pipe service to reduce the operational needs, this client
 * is introduced to directly call the pipe component instead of going over HTTP.
 *
 */
class DirectComponentBridgeClient implements Client
{
    private $validator;
    private $commandBus;
    private $deploymentRepository;
    private $environmentClientFactory;
    private $bucketRepository;
    private $kubernetesClientFactory;

    public function __construct(
        ValidatorInterface $validator,
        MessageBus $commandBus,
        DeploymentRepository $deploymentRepository,
        EnvironmentClientFactory $environmentClientFactory,
        BucketRepository $bucketRepository,
        KubernetesClientFactory $kubernetesClientFactory
    ) {
        $this->validator = $validator;
        $this->commandBus = $commandBus;
        $this->deploymentRepository = $deploymentRepository;
        $this->environmentClientFactory = $environmentClientFactory;
        $this->bucketRepository = $bucketRepository;
        $this->kubernetesClientFactory = $kubernetesClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function start(PipeDeploymentRequest $deploymentRequest, User $user)
    {
        $violations = $this->validator->validate($deploymentRequest);
        if (count($violations) > 0) {
            throw new PipeClientException($violations->get(0)->getMessage());
        }

        $deployment = PipeViewDeployment::fromRequest($deploymentRequest);
        $this->deploymentRepository->save($deployment);

        $this->commandBus->handle(new StartDeploymentCommand($deployment));

        return $this->deploymentRepository->find($deployment->getUuid());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEnvironment(Target $target, Team $team, User $authenticatedUser)
    {
        $client = $this->environmentClientFactory->getByCluster($this->getCluster($team, $target->getClusterIdentifier()));

        try {
            $environment = $client->find($target->getEnvironmentName());
        } catch (EnvironmentNotFound $e) {
            throw new PipeClientException($e->getMessage(), $e->getCode(), $e);
        }

        $client->delete($environment);
    }

    /**
     * {@inheritdoc}
     */
    public function deletePod(Team $team, User $authenticatedUser, string $clusterIdentifier, string $namespace, string $podName)
    {
        $cluster = $this->getCluster($team, $clusterIdentifier);
        $namespace = new KubernetesNamespace(new ObjectMetadata($namespace));
        $client = $this->kubernetesClientFactory->getByCluster($cluster);
        $namespaceClient = $client->getNamespaceClient($namespace);

        try {
            $podRepository = $namespaceClient->getPodRepository();
            $pod = $podRepository->findOneByName($podName);

            $podRepository->delete($pod);
        } catch (PodNotFound $e) {
            throw new PipeClientException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironments($clusterIdentifier, Team $team)
    {
        return $this->getEnvironmentsLabelled($clusterIdentifier, $team, []);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentsLabelled($clusterIdentifier, Team $team, array $labels)
    {
        try {
            $cluster = $this->getCluster($team, $clusterIdentifier);
            $environmentClient = $this->environmentClientFactory->getByCluster($cluster);

            if (!empty($labels)) {
                return promise_for($environmentClient->findByLabels($labels));
            }

            return promise_for($environmentClient->findAll());
        } catch (ServerError $e) {
            throw new PipeClientException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param Team $team
     * @param string $clusterIdentifier
     *
     * @throws PipeClientException
     *
     * @return Cluster
     */
    private function getCluster(Team $team, $clusterIdentifier)
    {
        $bucket = $this->bucketRepository->find($team->getBucketUuid());
        $matchingClusters = $bucket->getClusters()->filter(function (Cluster $cluster) use ($clusterIdentifier) {
            return $cluster->getIdentifier() == $clusterIdentifier;
        });

        if ($matchingClusters->count() == 0) {
            throw new PipeClientException(sprintf('Cluster "%s" is not found', $clusterIdentifier));
        }

        return $matchingClusters->first();
    }
}
