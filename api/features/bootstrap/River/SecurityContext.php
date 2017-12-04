<?php

namespace River;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Authenticator\Security\ApiKey\PredictableApiKeyUuidGenerator;
use ContinuousPipe\Authenticator\Team\TeamUsageLimitsRepository;
use ContinuousPipe\Security\Account\AccountRepository;
use ContinuousPipe\Security\Account\BitBucketAccount;
use ContinuousPipe\Security\ApiKey\UserApiKey;
use ContinuousPipe\Security\ApiKey\UserApiKeyRepository;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use ContinuousPipe\Security\Encryption\InMemory\PreviouslyKnownValuesVault;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\Team\TeamUsageLimits;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use ContinuousPipe\Security\User\UserRepository;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityContext implements Context
{
    const CACHE_SERVICE_ID = 'security.authenticator.cache';
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var PreviouslyKnownValuesVault
     */
    private $previouslyKnownValuesVault;
    /**
     * @var JWTManagerInterface
     */
    private $jwtManager;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @var User|null
     */
    private $currentUser;
    /**
     * @var UserApiKeyRepository
     */
    private $userApiKeyRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var TeamRepository
     */
    private $teamRepository;
    /**
     * @var BucketRepository
     */
    private $bucketRepository;
    /**
     * @var TeamUsageLimitsRepository
     */
    private $teamUsageLimitsRepository;
    /**
     * @var AccountRepository
     */
    private $accountRepository;
    /**
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;
    /**
     * @var PredictableApiKeyUuidGenerator
     */
    private $predictableApiKeyUuidGenerator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        KernelInterface $kernel,
        PreviouslyKnownValuesVault $previouslyKnownValuesVault,
        JWTManagerInterface $jwtManager,
        SerializerInterface $serializer,
        UserApiKeyRepository $userApiKeyRepository,
        UserRepository $userRepository,
        TeamRepository $teamRepository,
        BucketRepository $bucketRepository,
        TeamUsageLimitsRepository $teamUsageLimitsRepository,
        AccountRepository $accountRepository,
        TeamMembershipRepository $teamMembershipRepository,
        PredictableApiKeyUuidGenerator $predictableApiKeyUuidGenerator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->kernel = $kernel;
        $this->previouslyKnownValuesVault = $previouslyKnownValuesVault;
        $this->jwtManager = $jwtManager;
        $this->serializer = $serializer;
        $this->userApiKeyRepository = $userApiKeyRepository;
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
        $this->bucketRepository = $bucketRepository;
        $this->teamUsageLimitsRepository = $teamUsageLimitsRepository;
        $this->accountRepository = $accountRepository;
        $this->teamMembershipRepository = $teamMembershipRepository;
        $this->predictableApiKeyUuidGenerator = $predictableApiKeyUuidGenerator;
    }

    /**
     * @Given the created API key for the user :username will have the key :apiKey
     */
    public function theCreatedApiKeyForTheUserWillHaveTheKey($username, $apiKey)
    {
        $this->predictableApiKeyUuidGenerator->setUuidToGenerate(Uuid::fromString($apiKey));
    }

    /**
     * @Given I am authenticated
     */
    public function iAmAuthenticated()
    {
        $this->iAmAuthenticatedAs('samuel.roze@gmail.com');
    }

    /**
     * @Given the user :username have the API key :apiKey
     */
    public function theUserHaveTheApiKey($username, $apiKey)
    {
        $this->userApiKeyRepository->save(new UserApiKey(
            Uuid::uuid4(),
            $this->userRepository->findOneByUsername($username),
            $apiKey,
            new \DateTime(),
            $apiKey
        ));
    }

    /**
     * @Given I am authenticated as :username
     */
    public function iAmAuthenticatedAs($username)
    {
        $user = $this->thereIsAUser($username);

        $token = new JWTUserToken(['ROLE_USER']);
        $token->setUser(new SecurityUser($user));
        $this->tokenStorage->setToken($token);

        $this->currentUser = $user;
    }

    /**
     * @Given I am authenticated with the :role role
     */
    public function iAmAuthenticatedWithTheRole($role)
    {
        $token = new PreAuthenticatedToken($role, '', 'main', [$role]);
        $this->tokenStorage->setToken($token);
    }

    /**
     * @Given there is a user :username
     */
    public function thereIsAUser($username)
    {
        $user = new User($username, Uuid::uuid1());

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * @Given the team :slug exists
     * @Given there is a team :slug
     */
    public function theTeamExists($slug)
    {
        try {
            $team = $this->teamRepository->find($slug);
        } catch (TeamNotFound $e) {

            $bucket = new Bucket(Uuid::uuid1());
            $this->bucketRepository->save($bucket);

            $team = new Team($slug, $slug, $bucket->getUuid());
            $this->teamRepository->save($team);
        }

        return $team;
    }

    /**
     * @Given the team :team do not have any docker registry
     */
    public function theTeamDoNotHaveAnyDockerRegistry($team)
    {
        $registries = $this->findTeamBucket($team)->getDockerRegistries();

        foreach ($registries as $registry) {
            $registries->removeElement($registry);
        }
    }

    /**
     * @Given the team :slug has a :tidesPerHour tides per hour usage limit
     */
    public function theTeamHasATidesPerHourUsageLimit($slug, $tidesPerHour)
    {
        $team = $this->theTeamExists($slug);

        $this->teamUsageLimitsRepository->save(
            $team,
            new TeamUsageLimits($tidesPerHour)
        );
    }

    /**
     * @Given the user :username is :permission of the team :team
     */
    public function theUserIsOfTheTeam($username, $permission, $team)
    {
        $team = $this->teamRepository->find($team);
        $user = $this->userRepository->findOneByUsername($username);

        $this->teamMembershipRepository->save(new TeamMembership($team, $user, [$permission]));
    }

    /**
     * @Given the user :user is not in the team :team
     */
    public function theUserIsNotInTheTeam($user, $team)
    {
        $this->teamMembershipRepository->remove(new TeamMembership(
            $this->teamRepository->find($team),
            $this->userRepository->findOneByUsername($user)
        ));
    }

    /**
     * @Given the team :team have the credentials of a cluster :cluster
     * @Given the team :team have the credentials of a cluster :cluster with address :address
     */
    public function theTeamHaveTheCredentialsOfACluster($team, $cluster, $address = null)
    {
        $team = $this->teamRepository->find($team);
        $bucket = $this->bucketRepository->find($team->getBucketUuid());
        $address = $address ?: 'https://1.2.3.4';

        $bucket->getClusters()->add(new Kubernetes($cluster, $address, 'v1', '', ''));

        $this->bucketRepository->save($bucket);
    }

    /**
     * @Given the cluster :clusterIdentifier of the team :team have the following policies:
     */
    public function theClusterOfTheTeamHaveTheFollowingPolicies($clusterIdentifier, $team, PyStringNode $string)
    {
        $team = $this->teamRepository->find($team);
        $bucket = $this->bucketRepository->find($team->getBucketUuid());

        $bucket->getClusters()->add(new Kubernetes($clusterIdentifier, 'https://1.2.3.4', 'v1', '', '',
            $this->serializer->deserialize($string->getRaw(), 'array<'.Cluster\ClusterPolicy::class.'>', 'json')
        ));

        $this->bucketRepository->save($bucket);
    }

    /**
     * @Given the team :team have the credentials of a Docker registry :registry
     * @Given the team :team have the credentials of a Docker registry :registry with the username :username
     */
    public function theTeamHaveTheCredentialsOfADockerRegistry($team, $registry, $username = null)
    {
        $this->addRegistryToTeam($team, new DockerRegistry($username ?: 'username', 'password', 'email@example.com', $registry));
    }

    /**
     * @Given the team :team have the credentials of the following Docker registry:
     * @Given the team :team have the credentials of the following Docker registries:
     */
    public function theTeamHaveTheCredentialsOfTheFollowingDockerRegistries($team, TableNode $table)
    {
        foreach ($table->getHash() as $registryAsArray) {
            if (isset($registryAsArray['attributes'])) {
                $registryAsArray['attributes'] = json_decode($registryAsArray['attributes'], true);
            }

            $this->addRegistryToTeam(
                $team,
                $this->serializer->deserialize(json_encode($registryAsArray), DockerRegistry::class, 'json')
            );
        }
    }

    /**
     * @Then the team :teamSlug should have one cluster named :clusterName
     */
    public function theTeamShouldHaveOneClusterNamed($teamSlug, $clusterName)
    {
        $clusters = $this->findMatchingClusters($teamSlug, $clusterName);

        if ($clusters->count() != 1) {
            throw new \RuntimeException(sprintf('Found %d clusters', $clusters->count()));
        }
    }

    /**
     * @Then the team :teamSlug should have a cluster named :clusterName
     */
    public function theTeamShouldHaveAClusterNamed($teamSlug, $clusterName)
    {
        $clusters = $this->findMatchingClusters($teamSlug, $clusterName);

        if ($clusters->count() == 0) {
            throw new \RuntimeException('Cluster was not found');
        }
    }

    /**
     * @Then the team :teamSlug should have docker credentials for :address with the username :username
     */
    public function theTeamShouldHaveDockerCredentialsForWithTheUsername($teamSlug, $address, $username)
    {
        $registry = $this->registryFromTeam($teamSlug, $address);

        if ($registry->getUsername() != $username) {
            throw new \RuntimeException(sprintf(
                'Found username "%s" instead',
                $registry->getUsername()
            ));
        }
    }

    private function registryFromTeam(string $teamSlug, string $address)
    {
        foreach ($this->findTeamBucket($teamSlug)->getDockerRegistries() as $registry) {
            if ($registry->getServerAddress() == $address || $registry->getFullAddress() == $address) {
                return $registry;
            }
        }

        return null;
    }

    /**
     * @Then the team :teamSlug should have docker credentials for :address with the attribute :attributeName valued :attributeValue
     */
    public function theTeamShouldHaveDockerCredentialsForWithTheAttributeValued($teamSlug, $address, $attributeName, $attributeValue)
    {
        $registry = $this->registryFromTeam($teamSlug, $address);

        if (!isset($registry->getAttributes()[$attributeName])) {
            throw new \RuntimeException(sprintf(
                'Attribute "%s" not found',
                $attributeName
            ));
        }

        if ($registry->getAttributes()[$attributeName] != $attributeValue) {
            throw new \RuntimeException(sprintf(
                'Value "%s" found instead',
                $registry->getAttributes()[$attributeName]
            ));
        }
    }

    /**
     * @Given the user :username is a ghost
     */
    public function theUserIsAGhost($username)
    {
        $user = $this->userRepository->findOneByUsername($username);
        $user->setRoles(array_merge($user->getRoles(), ['ROLE_GHOST']));

        $this->userRepository->save($user);
    }

    /**
     * @Given I have a BitBucket account :uuid for the user :username
     */
    public function iHaveABitbucketAccountForTheUser($uuid, $username)
    {
        $this->accountRepository->link(
            $this->currentUser->getUsername(),
            new BitBucketAccount(
                Uuid::fromString($uuid),
                $username,
                $username,
                $username.'@example.com',
                'refresh-token'
            )
        );
    }

    /**
     * @Given I send a :method request to the path :path
     */
    public function iSendARequestToThePath($method, $path)
    {
        $this->response = $this->kernel->handle(Request::create($path, $method));
    }

    /**
     * @Then the status code of the response should be :code
     */
    public function theStatusCodeOfTheResponseShouldBe($code)
    {
        if ($this->response->getStatusCode() != $code) {
            throw new \RuntimeException(sprintf(
                'Expected code %d but got %d',
                $code,
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Given the encrypted version of the value :plainValue for the flow :flowUuid will be :encryptedValue
     */
    public function theEncryptedVersionOfTheValueForTheFlowWillBe($plainValue, $flowUuid, $encryptedValue)
    {
        $this->previouslyKnownValuesVault->addEncryptionMapping(
            'flow-'.$flowUuid,
            $plainValue,
            $encryptedValue
        );
    }

    /**
     * @Given the decrypted version of the value :encryptedValue for the flow :flowUuid will be :plainValue
     */
    public function theDecryptedVersionOfTheValueForTheFlowWillBe($encryptedValue, $flowUuid, $plainValue)
    {
        $this->previouslyKnownValuesVault->addDecryptionMapping(
            'flow-'.$flowUuid,
            $encryptedValue,
            $plainValue
        );
    }

    private function findMatchingClusters($teamSlug, $clusterName) : Collection
    {
        return $this->findTeamBucket($teamSlug)->getClusters()->filter(function(Cluster $cluster) use ($clusterName) {
            return $cluster->getIdentifier() == $clusterName;
        });
    }

    private function findTeamBucket($teamSlug) : Bucket
    {
        return $this->bucketRepository->find(
            $this->teamRepository->find($teamSlug)->getBucketUuid()
        );
    }

    private function addRegistryToTeam(string $team, DockerRegistry $registry)
    {
        $team = $this->teamRepository->find($team);
        $bucket = $this->bucketRepository->find($team->getBucketUuid());

        $bucket->getDockerRegistries()->add($registry);

        $this->bucketRepository->save($bucket);
    }

    public function tokenForUser($username)
    {
        return $this->jwtManager->create(new \Symfony\Component\Security\Core\User\User($username, null));
    }
}
