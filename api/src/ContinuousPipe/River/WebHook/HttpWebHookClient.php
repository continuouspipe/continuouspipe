<?php

namespace ContinuousPipe\River\WebHook;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\SerializerInterface;

class HttpWebHookClient implements WebHookClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ClientInterface $client
     * @param SerializerInterface $serializer
     */
    public function __construct(ClientInterface $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function send(WebHook $webHook)
    {
        try {
            $this->client->post($webHook->getUrl(), [
                'json' => $this->getNormalizedWebHookBody($webHook),
            ]);
        } catch (RequestException $e) {
            throw new WebHookException('Unable to send web-hook: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param WebHook $webHook
     *
     * @return array
     */
    private function getNormalizedWebHookBody(WebHook $webHook)
    {
        return json_decode($this->serializer->serialize($webHook, 'json'), true);
    }
}
