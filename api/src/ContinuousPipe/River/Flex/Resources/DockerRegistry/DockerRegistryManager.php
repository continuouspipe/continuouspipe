<?php

namespace ContinuousPipe\River\Flex\Resources\DockerRegistry;

use ContinuousPipe\QuayIo\QuayClient;
use ContinuousPipe\QuayIo\QuayException;
use ContinuousPipe\QuayIo\RepositoryAlreadyExists;
use ContinuousPipe\QuayIo\RobotAccount;
use ContinuousPipe\River\Flex\Resources\FlexResourcesException;
use ContinuousPipe\River\Flow;
use ContinuousPipe\Security\Authenticator\AuthenticatorClient;
use ContinuousPipe\Security\Authenticator\AuthenticatorException;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use ContinuousPipe\Security\Team\Team;

class DockerRegistryManager
{
    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;

    /**
     * @var QuayClient
     */
    private $quayClient;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    public function __construct(AuthenticatorClient $authenticatorClient, QuayClient $quayClient, BucketRepository $bucketRepository)
    {
        $this->authenticatorClient = $authenticatorClient;
        $this->quayClient = $quayClient;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * @param Flow $flow
     *
     * @throws FlexResourcesException
     */
    public function createRepositoryForFlow(Flow $flow)
    {
        $bucket = $this->bucketRepository->find($flow->getTeam()->getBucketUuid());

        try {
            $repository = $this->quayClient->createRepository('flow-' . $flow->getUuid()->toString());
        } catch (RepositoryAlreadyExists $e) {
            $repository = $e->getRepository();
        } catch (QuayException $e) {
            throw new FlexResourcesException('Could not create a Docker registry', $e->getCode(), $e);
        }

        $fullRegistryAddress = 'quay.io/'.$repository->getName();
        if (null === ($registry = $this->getFlexDockerRegistryCredentials($bucket, $fullRegistryAddress))) {
            try {
                $robot = $this->generateRobotAccount($flow->getTeam());
            } catch (QuayException $e) {
                throw new FlexResourcesException('Could not create registry robot account', $e->getCode(), $e);
            }

            $registry = new DockerRegistry(
                $robot->getUsername(),
                $robot->getPassword(),
                $robot->getEmail(),
                null,
                $fullRegistryAddress,
                [
                    'managed' => true,
                    'visibility' => $repository->getVisibility(),
                    'flow' => $flow->getUuid()->toString(),
                ]
            );

            try {
                $this->authenticatorClient->addDockerRegistryToBucket(
                    $bucket->getUuid(),
                    $registry
                );
            } catch (AuthenticatorException $e) {
                throw new FlexResourcesException('Could not save created Docker registry into project\'s credentials', $e->getCode(), $e);
            }
        }

        try {
            $this->quayClient->allowUserToAccessRepository(
                $registry->getUsername(),
                $repository->getName()
            );
        } catch (QuayException $e) {
            throw new FlexResourcesException('Could not allow user to access Docker Registry repository', $e->getCode(), $e);
        }
    }

    /**
     * @param DockerRegistry $registry
     * @param string $visibility
     *
     * @throws FlexResourcesException
     */
    public function changeVisibility(DockerRegistry $registry, string $visibility)
    {
        if (strpos($registry->getFullAddress(), 'quay.io/') !== 0) {
            throw new FlexResourcesException('Only supports quay.io docker registries');
        }

        $name = substr($registry->getFullAddress(), strlen('quay.io/'));

        $this->quayClient->changeVisibility($name, $visibility);
    }

    private function generateRobotAccount(Team $team) : RobotAccount
    {
        $robotAccountName = $this->getDockerRegistryRobotAccountName($team);

        return $this->quayClient->createRobotAccount($robotAccountName);
    }

    private function getDockerRegistryRobotAccountName(Team $team) : string
    {
        return 'project-'.$team->getSlug();
    }

    private function getFlexDockerRegistryCredentials(Bucket $bucket, string $fullAddress)
    {
        $quayCredentials = $bucket->getDockerRegistries()->filter(function (DockerRegistry $credentials) use ($fullAddress) {
            return $credentials->getFullAddress() == $fullAddress;
        });

        return !$quayCredentials->isEmpty() ? $quayCredentials->first() : null;
    }
}
