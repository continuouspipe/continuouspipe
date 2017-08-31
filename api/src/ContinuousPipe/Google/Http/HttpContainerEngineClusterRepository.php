<?php

namespace ContinuousPipe\Google\Http;

use ContinuousPipe\Google\ContainerEngineCluster;
use ContinuousPipe\Google\ContainerEngineClusterList;
use ContinuousPipe\Google\ContainerEngineClusterRepository;
use ContinuousPipe\Google\GoogleException;
use ContinuousPipe\Security\Account\GoogleAccount;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\SerializerInterface;

class HttpContainerEngineClusterRepository implements ContainerEngineClusterRepository
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
    public function findAll(GoogleAccount $account, string $project)
    {
        $client = $this->clientFactory->fromAccount($account);

        $zonesPromise = $client->requestAsync('GET', 'https://www.googleapis.com/compute/beta/projects/'.$project.'/zones')
            ->then(function (Response $response) {
                $contents = $response->getBody()->getContents();
                $json = \GuzzleHttp\json_decode($contents, true);

                if (!array_key_exists('items', $json)) {
                    throw new \InvalidArgumentException('The result should contain an `items` key');
                }

                return $json['items'];
            })
        ;

        try {
            $zones = $zonesPromise->wait();
        } catch (RequestException $e) {
            throw GoogleHttpUtils::createGoogleExceptionFromRequestException($e);
        }

        $clustersPromises = array_map(function (array $zone) use ($client, $project) {
            $url = sprintf(
                'https://container.googleapis.com/v1/projects/%s/zones/%s/clusters',
                $project,
                $zone['name']
            );

            return $client->requestAsync('GET', $url)->then(function (Response $response) {
                $contents = $response->getBody()->getContents();

                echo $contents;

                return $this->serializer->deserialize($contents, ContainerEngineClusterList::class, 'json');
            })->then(function (ContainerEngineClusterList $clusterList) {
                return $clusterList->getClusters();
            });
        }, $zones);

        try {
            $results = \GuzzleHttp\Promise\unwrap($clustersPromises);
        } catch (RequestException $e) {
            throw GoogleHttpUtils::createGoogleExceptionFromRequestException($e);
        }

        return array_reduce($results, function (array $carry, array $clusters) {
            return array_merge($carry, $clusters);
        }, []);
    }

    /**
     * {@inheritdoc}
     */
    public function find(GoogleAccount $account, string $project, string $clusterIdentifier): ContainerEngineCluster
    {
        $clusters = $this->findAll($account, $project);
        $foundClusterNames = [];

        foreach ($clusters as $cluster) {
            if ($cluster->getName() == $clusterIdentifier) {
                return $cluster;
            }

            $foundClusterNames[] = $cluster->getName();
        }

        throw new GoogleException(sprintf(
            'Did not find cluster named "%s" but found: %s',
            $clusterIdentifier,
            implode(', ', $foundClusterNames)
        ));
    }
}
