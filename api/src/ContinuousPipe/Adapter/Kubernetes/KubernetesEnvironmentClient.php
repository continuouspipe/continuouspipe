<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\EnvironmentClient;
use ContinuousPipe\Adapter\EnvironmentNotFound;
use ContinuousPipe\Adapter\Events;
use ContinuousPipe\Adapter\Kubernetes\Event\Environment\EnvironmentDeletionEvent;
use ContinuousPipe\Adapter\Kubernetes\Inspector\NamespaceInspector;
use ContinuousPipe\Model\Environment;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\unwrap;
use Kubernetes\Client\Client;
use Kubernetes\Client\Exception\ClientError;
use Kubernetes\Client\Exception\NamespaceNotFound;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\Label;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Kubernetes\Client\Model\NamespaceList;

class KubernetesEnvironmentClient implements EnvironmentClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var NamespaceInspector
     */
    private $namespaceInspector;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Client $client,
        NamespaceInspector $namespaceInspector,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->namespaceInspector = $namespaceInspector;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $namespaces = $this->getNamespaceRepository()->findAll();

        return $this->namespacesToEnvironments($namespaces);
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels)
    {
        $namespaceLabels = KeyValueObjectList::fromAssociativeArray($labels, Label::class);
        $namespaces = $this->getNamespaceRepository()->findByLabels($namespaceLabels);

        return $this->namespacesToEnvironments($namespaces);
    }

    /**
     * {@inheritdoc}
     */
    public function find($identifier)
    {
        try {
            $namespace = $this->getNamespaceRepository()->findOneByName($identifier);
        } catch (NamespaceNotFound $e) {
            throw new EnvironmentNotFound(sprintf(
                'The environment "%s" is not found',
                $identifier
            ), 400, $e);
        }

        return $this->namespaceToEnvironment($namespace)->wait();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Environment $environment)
    {
        $namespaceRepository = $this->getNamespaceRepository();

        try {
            $namespace = $namespaceRepository->findOneByName($environment->getIdentifier());
        } catch (NamespaceNotFound $e) {
            throw new EnvironmentNotFound('Environment "'.$environment->getIdentifier().'" is not found', $e->getCode(), $e);
        }

        $this->eventDispatcher->dispatch(
            Events::ENVIRONMENT_PRE_DELETION,
            new EnvironmentDeletionEvent($this->client->getNamespaceClient($namespace), $environment)
        );

        try {
            $namespaceRepository->delete($namespace);
        } catch (ClientError $e) {
            if ($e->getStatus()->getCode() != Response::HTTP_CONFLICT) {
                throw $e;
            }

            $this->logger->warning('The delete request returned a conflict exception; ignoring.', [
                'message' => $e->getMessage(),
                'environment' => $environment->getIdentifier(),
            ]);
        }
    }

    /**
     * Convert the given namespaces into environments.
     *
     * @param NamespaceList $namespaces
     *
     * @return Environment[]
     */
    private function namespacesToEnvironments(NamespaceList $namespaces)
    {
        $environmentPromises = [];

        foreach ($namespaces->getNamespaces() as $namespace) {
            $environmentPromises[] = $this->namespaceToEnvironment($namespace);
        }

        return unwrap($environmentPromises);
    }

    /**
     * @param KubernetesNamespace $namespace
     *
     * @return PromiseInterface
     */
    private function namespaceToEnvironment(KubernetesNamespace $namespace)
    {
        $namespaceClient = $this->client->getNamespaceClient($namespace);

        return $this->namespaceInspector->getComponents($namespaceClient)->then(function (array $components) use ($namespace) {
            $namespaceMetadata = $namespace->getMetadata();

            return new Environment(
                $namespaceMetadata->getName(),
                $namespaceMetadata->getName(),
                $components,
                null,
                [],
                (null !== $status = $namespace->getStatus()) ? $status->getPhase() : null
            );
        });
    }

    /**
     * @return \Kubernetes\Client\Repository\NamespaceRepository
     */
    private function getNamespaceRepository()
    {
        return $this->client->getNamespaceRepository();
    }
}
