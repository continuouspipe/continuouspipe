<?php

namespace ContinuousPipe\Builder\Client;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;

class HttpBuilderClient implements BuilderClient
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $baseUrl;
    /**
     * @var JWTManagerInterface
     */
    private $jwtManager;

    /**
     * @param ClientInterface     $httpClient
     * @param SerializerInterface $serializer
     * @param JWTManagerInterface $jwtManager
     * @param string              $baseUrl
     */
    public function __construct(ClientInterface $httpClient, SerializerInterface $serializer, JWTManagerInterface $jwtManager, $baseUrl)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = $baseUrl;
        $this->serializer = $serializer;
        $this->jwtManager = $jwtManager;
    }

    /**
     * {@inheritdoc}
     */
    public function build(BuildRequest $buildRequest, User $user)
    {
        $token = $this->jwtManager->create(new SecurityUser($user));

        try {
            $response = $this->httpClient->request('POST', $this->baseUrl . '/build', [
                'body' => $this->serializer->serialize($buildRequest, 'json'),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);
        } catch (RequestException $e) {
            if (null !== ($response = $e->getResponse())) {
                try {
                    $contents = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

                    if (isset($contents['error']['message'])) {
                        throw new BuilderException(
                            $contents['error']['message'],
                            isset($contents['error']['code']) ? $contents['error']['code'] : $e->getCode(),
                            $e
                        );
                    }
                } catch (\InvalidArgumentException $errorException) {
                    // Handle the exception as if it wasn't supported
                }
            }

            throw new BuilderException($e->getMessage(), $e->getCode(), $e);
        }

        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->seek(0);
        }

        $build = $this->serializer->deserialize($body->getContents(), BuilderBuild::class, 'json');

        return $build;
    }
}
