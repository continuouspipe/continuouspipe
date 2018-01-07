<?php

namespace ContinuousPipe\Google\Http;

use ContinuousPipe\Google\GoogleException;
use ContinuousPipe\Google\ProjectList;
use ContinuousPipe\Google\ProjectRepository;
use ContinuousPipe\Security\Account\GoogleAccount;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\SerializerInterface;

class HttpProjectRepository implements ProjectRepository
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @param ClientFactory       $clientFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(ClientFactory $clientFactory, SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->clientFactory = $clientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(GoogleAccount $account)
    {
        $client = $this->clientFactory->fromAccount($account);

        try {
            $response = $client->request('GET', 'https://cloudresourcemanager.googleapis.com/v1beta1/projects');
        } catch (RequestException $e) {
            throw GoogleHttpUtils::createGoogleExceptionFromRequestException($e);
        }

        try {
            $list = $this->serializer->deserialize($response->getBody()->getContents(), ProjectList::class, 'json');
        } catch (\Exception $e) {
            throw new GoogleException('Unexpected response ('.$response->getStatusCode().')', 500, $e);
        }

        return $list->getProjects();
    }
}
