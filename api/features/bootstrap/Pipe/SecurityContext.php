<?php

namespace Pipe;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Pipe\Kubernetes\Tests\Repository\InMemoryNetworkPolicyRepository;
use ContinuousPipe\Pipe\Kubernetes\Tests\Repository\RBAC\InMemoryRoleBindingRepository;
use ContinuousPipe\Pipe\Kubernetes\Tests\Repository\Trace\RBAC\TraceableRoleBindingRepository;
use ContinuousPipe\Pipe\Kubernetes\Tests\Repository\Trace\TraceableNetworkPolicyRepository;
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
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\RBAC\RoleBinding;
use Kubernetes\Client\Model\RBAC\RoleRef;
use Kubernetes\Client\Model\RBAC\Subject;
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
    /**
     * @var TraceableRoleBindingRepository
     */
    private $traceableRoleBindingRepository;
    /**
     * @var InMemoryRoleBindingRepository
     */
    private $inMemoryRoleBindingRepository;
    /**
     * @var TraceableNetworkPolicyRepository
     */
    private $traceableNetworkPolicyRepository;
    /**
     * @var InMemoryNetworkPolicyRepository
     */
    private $inMemoryNetworkPolicyRepository;

    public function __construct(
        BucketRepository $bucketRepository,
        Serializer $serializer,
        TeamRepository $teamRepository,
        PreviouslyKnownValuesVault $previouslyKnownValuesVault,
        TraceableRoleBindingRepository $traceableRoleBindingRepository,
        InMemoryRoleBindingRepository $inMemoryRoleBindingRepository,
        TraceableNetworkPolicyRepository $traceableNetworkPolicyRepository,
        InMemoryNetworkPolicyRepository $inMemoryNetworkPolicyRepository
    ) {
        $this->bucketRepository = $bucketRepository;
        $this->serializer = $serializer;
        $this->teamRepository = $teamRepository;
        $this->previouslyKnownValuesVault = $previouslyKnownValuesVault;
        $this->traceableRoleBindingRepository = $traceableRoleBindingRepository;
        $this->inMemoryRoleBindingRepository = $inMemoryRoleBindingRepository;
        $this->traceableNetworkPolicyRepository = $traceableNetworkPolicyRepository;
        $this->inMemoryNetworkPolicyRepository = $inMemoryNetworkPolicyRepository;
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
     * @Given the user credentials of the cluster :clusterIdentifier of bucket :bucketUuid is a Google Cloud service account for the user :username
     */
    public function theUserCredentialsOfTheClusterOfBucketIsAGoogleCloudServiceAccountForTheUser($clusterIdentifier, $bucketUuid, $username)
    {
        $this->updateClusterNamed($bucketUuid, $clusterIdentifier, function (Cluster $cluster) use ($username) {
            if (!$cluster instanceof Cluster\Kubernetes) {
                throw new \RuntimeException('Can only update Kubernetes clusters');
            }

            $serviceAccount = base64_encode(json_encode([
                'type' => 'service_account',
                'client_email' => $username,
            ]));

            return new Cluster\Kubernetes(
                $cluster->getIdentifier(),
                $cluster->getAddress(),
                $cluster->getVersion(),
                null,
                null,
                $cluster->getPolicies(),
                null,
                $cluster->getCaCertificate(),
                $serviceAccount,
                $cluster->getManagementCredentials()
            );
        });
    }

    /**
     * @Given the cluster :clusterIdentifier of the bucket :bucketUuid has the :policyName policy with the following configuration:
     */
    public function theClusterHasThePolicy($clusterIdentifier, $bucketUuid, $policyName, PyStringNode $configuration)
    {
        $this->updateClusterNamed($bucketUuid, $clusterIdentifier, function (Cluster $cluster) use ($policyName, $configuration) {
            return $cluster->withPolicies([
                new Cluster\ClusterPolicy($policyName, \GuzzleHttp\json_decode($configuration->getRaw(), true)),
            ]);
        });
    }

    /**
     * @Given the namespace contains a role binding named :name
     */
    public function theNamespaceContainsARoleBindingNamed($name)
    {
        $this->inMemoryRoleBindingRepository->create(new RoleBinding(
            new ObjectMetadata($name),
            new RoleRef('rbac.authorization.k8s.io', 'ClusterRole', 'role-name'),
            []
        ));
    }

    /**
     * @Then the user :username should be bound to the cluster role :clusterRoleName in the namespace :namespace
     */
    public function theUserShouldBeBoundToTheClusterRoleInTheNamespace($username, $clusterRoleName, $namespace)
    {
        foreach ($this->traceableRoleBindingRepository->getCreated() as $binding) {
            if (
                $binding->getRoleRef()->getKind() == 'ClusterRole'
                && $binding->getRoleRef()->getName() == $clusterRoleName
                && $this->hasSubject($binding->getSubjects(), $username, 'User')
            ) {
                return;
            }
        }

        throw new \RuntimeException('No created role binding found');
    }

    /**
     * @Then no role binding should be created
     */
    public function noRoleBindingShouldBeCreated()
    {
        $createdBindings = $this->traceableRoleBindingRepository->getCreated();

        if (count($createdBindings) > 0) {
            throw new \RuntimeException(sprintf(
                'Found %d created bindings instead of 0',
                count($createdBindings)
            ));
        }
    }

    /**
     * @Then the network policy :name should be created
     */
    public function theNetworkPolicyShouldBeCreated($name)
    {
        foreach ($this->traceableNetworkPolicyRepository->getCreated() as $policy) {
            if ($policy->getMetadata()->getName() == $name) {
                return $policy;
            }
        }

        throw new \RuntimeException('Network policy not found in the created list');
    }

    /**
     * @Then the network policy :name should have no egress configuration
     */
    public function theNetworkPolicyShouldHaveNoEgressConfiguration($name)
    {
        $policy = $this->theNetworkPolicyShouldBeCreated($name);

        if (!empty($policy->getSpec()->getEgress())) {
            throw new \RuntimeException('Found some egress configuration');
        }
    }

    /**
     * @Then the network policy :name should have an ingress rule from :selector
     */
    public function theNetworkPolicyShouldHaveAnIngressRuleFrom($name, $selector)
    {
        $policy = $this->theNetworkPolicyShouldBeCreated($name);
        $expectedFrom = [
            substr($selector, 0, strpos($selector,'=')) => substr($selector, strpos($selector, '=') + 1),
        ];

        foreach ($policy->getSpec()->getIngress() as $ingressRule) {
            foreach ($ingressRule->getFrom() as $from) {
                if ($from->getNamespaceSelector() !== null && $from->getNamespaceSelector()->getMatchLabels() == $expectedFrom) {
                    return;
                }
            }
        }

        throw new \RuntimeException('No such selector or ingress rule found');
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

    private function updateClusterNamed(string $bucketUuid, string $clusterIdentifier, callable $updateCallable)
    {
        $bucket = $this->bucketRepository->find(Uuid::fromString($bucketUuid));
        foreach ($bucket->getClusters() as $key => $cluster) {
            if ($cluster->getIdentifier() == $clusterIdentifier) {
                $bucket->getClusters()->set($key, $updateCallable($cluster));

                return;
            }
        }

        throw new \RuntimeException(sprintf(
            'Cluster "%s" not found in bucket',
            $clusterIdentifier
        ));
    }

    /**
     * @param Subject[] $subjects
     * @param string $username
     * @param string $kind
     *
     * @return bool
     */
    private function hasSubject(array $subjects, string $username, string $kind) : bool
    {
        foreach ($subjects as $subject) {
            if ($subject->getKind() == $kind && $subject->getName() == $username) {
                return true;
            }
        }

        return false;
    }
}
