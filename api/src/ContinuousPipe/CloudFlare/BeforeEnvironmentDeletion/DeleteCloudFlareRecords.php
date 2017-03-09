<?php

namespace ContinuousPipe\CloudFlare\BeforeEnvironmentDeletion;

use ContinuousPipe\Adapter\Events;
use ContinuousPipe\Adapter\Kubernetes\Event\Environment\EnvironmentDeletionEvent;
use ContinuousPipe\CloudFlare\CloudFlareClient;
use ContinuousPipe\CloudFlare\Encryption\EncryptedAuthentication;
use ContinuousPipe\CloudFlare\Encryption\EncryptionNamespace;
use ContinuousPipe\Security\Encryption\Vault;
use Kubernetes\Client\Exception\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeleteCloudFlareRecords implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CloudFlareClient
     */
    private $cloudFlareClient;
    /**
     * @var Vault
     */
    private $vault;

    public function __construct(LoggerInterface $logger, CloudFlareClient $cloudFlareClient, Vault $vault)
    {
        $this->logger = $logger;
        $this->cloudFlareClient = $cloudFlareClient;
        $this->vault = $vault;
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
            $this->logger->warning('Unable to get the services of the namespace', [
                'exception' => $e,
            ]);

            return;
        }

        foreach ($services as $service) {
            $annotation = $service->getMetadata()->getAnnotationList()->get('com.continuouspipe.io.cloudflare.zone');
            if (null === $annotation) {
                continue;
            }

            try {
                $json = \GuzzleHttp\json_decode($annotation->getValue(), true);

                if (!isset($json['record_identifier']) || !isset($json['record_identifier']) || !isset($json['encrypted_authentication'])) {
                    throw new \InvalidArgumentException('The JSON object do not contain the required fields');
                }
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning('The content of the annotation `com.continuouspipe.io.cloudflare.zone` is not readable', [
                    'value' => $annotation->getValue(),
                    'exception' => $e,
                ]);

                continue;
            }

            $encryptedAuthentication = new EncryptedAuthentication(
                $this->vault,
                EncryptionNamespace::from($json['zone_identifier'], $json['record_identifier'])
            );

            $authentication = $encryptedAuthentication->decrypt($json['encrypted_authentication']);

            $this->cloudFlareClient->deleteRecord($json['zone_identifier'], $authentication, $json['record_identifier']);
        }
    }
}
