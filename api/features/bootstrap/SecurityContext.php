<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Pipe\Uuid\UuidTransformer;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Encryption\InMemory\PreviouslyKnownValuesVault;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamRepository;
use JMS\Serializer\Serializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class SecurityContext implements Context
{
    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var PreviouslyKnownValuesVault
     */
    private $previouslyKnownValuesVault;

    public function __construct(
        BucketRepository $bucketRepository,
        Serializer $serializer,
        TeamRepository $teamRepository,
        PreviouslyKnownValuesVault $previouslyKnownValuesVault
    ) {
        $this->bucketRepository = $bucketRepository;
        $this->serializer = $serializer;
        $this->teamRepository = $teamRepository;
        $this->previouslyKnownValuesVault = $previouslyKnownValuesVault;
    }

    /**
     * @Given there is a cluster in the bucket :bucket with the following configuration:
     */
    public function thereIsAClusterInTheBucketWithTheFollowingConfiguration($uuid, TableNode $table)
    {
        $clusterConfiguration = $table->getHash()[0];
        $cluster = $this->serializer->deserialize(json_encode($clusterConfiguration), Cluster::class, 'json');

        $bucketUuid = UuidTransformer::transform(Uuid::fromString($uuid));
        try {
            $bucket = $this->bucketRepository->find($bucketUuid);
        } catch (BucketNotFound $e) {
            $bucket = new Bucket($bucketUuid);
        }

        $bucket->getClusters()->add($cluster);

        $this->bucketRepository->save($bucket);
    }

    /**
     * @Given the cluster :clusterIdentifier of the bucket :bucketUuid has the :policyName policy
     */
    public function theClusterHasThePolicy($clusterIdentifier, $bucketUuid, $policyName)
    {
        $cluster = $this->clusterFromBucket(Uuid::fromString($bucketUuid), $clusterIdentifier);
        $cluster->setPolicies([
            new Cluster\ClusterPolicy($policyName),
        ]);
    }

    /**
     * @Given the bucket of the team :teamName is the bucket :bucketUuid
     */
    public function theBucketOfTheTeamIsTheBucket($teamName, $bucketUuid)
    {
        $uuid = UuidTransformer::transform(Uuid::fromString($bucketUuid));

        try {
            $team = $this->teamRepository->find($teamName);
            $team->setBucketUuid($uuid);
        } catch (TeamNotFound $e) {
            $team = new Team($teamName, $teamName, $uuid);
        }

        $this->teamRepository->save($team);
    }

    /**
     * @Given the encrypted value :encryptedValue in the namespace :namespace will be decrypted as the following by the vault:
     */
    public function theEncryptedValueInTheNamespaceWillBeDecryptedAsTheFollowingByTheVault($encryptedValue, $namespace, PyStringNode $string)
    {
        $this->previouslyKnownValuesVault->addDecryptionMapping($namespace, $encryptedValue, $string->getRaw());
    }

    /**
     * @param UuidInterface $bucketUuid
     * @param string $clusterIdentifier
     *
     * @return Cluster
     */
    private function clusterFromBucket(UuidInterface $bucketUuid, $clusterIdentifier)
    {
        $bucket = $this->bucketRepository->find($bucketUuid);
        foreach ($bucket->getClusters() as $cluster) {
            if ($cluster->getIdentifier() == $clusterIdentifier) {
                return $cluster;
            }
        }

        throw new \RuntimeException(sprintf(
            'Cluster "%s" not found in bucket',
            $clusterIdentifier
        ));
    }
}
