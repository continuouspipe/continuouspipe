<?php

namespace GitHub\WebHook\Setup;

use Github\Client;
use GitHub\WebHook\Model\Repository;
use GitHub\WebHook\Model\WebHook;
use JMS\Serializer\SerializerInterface;

class HttpWebHookManager implements WebHookManager
{
    /**
     * @var Client
     */
    private $githubClient;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Client              $githubClient
     * @param SerializerInterface $serializer
     */
    public function __construct(Client $githubClient, SerializerInterface $serializer)
    {
        $this->githubClient = $githubClient;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function setup(Repository $repository, WebHook $webHook)
    {
        $webHookJson = $this->serializer->serialize($webHook, 'json');

        $rawCreatedWebhook = $this->githubClient->repo()->hooks()->create(
            $repository->getOwner()->getLogin(),
            $repository->getName(),
            json_decode($webHookJson, true)
        );

        $createdWebHook = $this->serializer->deserialize(json_encode($rawCreatedWebhook), WebHook::class, 'json');

        return $createdWebHook;
    }
}
