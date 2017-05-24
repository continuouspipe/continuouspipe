<?php

namespace ContinuousPipe\HttpLabs\BeforeEnvironmentDeletion;

use ContinuousPipe\Adapter\Events;
use ContinuousPipe\Adapter\Kubernetes\Event\Environment\EnvironmentDeletionEvent;
use ContinuousPipe\HttpLabs\Client\HttpLabsClient;
use ContinuousPipe\Security\Encryption\Vault;
use Kubernetes\Client\Exception\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeleteHttpLabsStack implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Vault
     */
    private $vault;

    /**
     * @var HttpLabsClient
     */
    private $httpLabsClient;

    public function __construct(LoggerInterface $logger, HttpLabsClient $httpLabsClient, Vault $vault)
    {
        $this->logger = $logger;
        $this->vault = $vault;
        $this->httpLabsClient = $httpLabsClient;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::ENVIRONMENT_PRE_DELETION => 'beforeEnvironmentDeletion',
        ];
    }

    public function beforeEnvironmentDeletion(EnvironmentDeletionEvent $event)
    {
        try {
            $services = $event->getClient()->getServiceRepository()->findAll()->getServices();
        } catch (Exception $e) {
            $this->logger->warning(
                'Unable to get the services of the namespace',
                [
                    'exception' => $e,
                ]
            );

            return;
        }

        foreach ($services as $service) {
            $annotation = $service->getMetadata()->getAnnotationList()->get('com.continuouspipe.io.httplabs.stack');
            if (null === $annotation) {
                continue;
            }

            try {
                $metadata = \GuzzleHttp\json_decode($annotation->getValue(), true);

                $apiKey = "I don't know where to get this from!";
var_dump('here');
                $this->httpLabsClient->deleteStack(
                    $apiKey,
                    $metadata['stack_identifier']
                );
            } catch (\Throwable $e) {
                var_dump($e->getMessage());
                $this->logger->warning(
                    'Something went wrong while deleting the HttpLabs stack',
                    [
                        'exception' => $e,
                    ]
                );
            }
        }
    }
}
