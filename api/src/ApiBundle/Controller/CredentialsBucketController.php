<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use ContinuousPipe\Security\Credentials\GitHubToken;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use FOS\RestBundle\View\View as FOSRestView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/bucket/{bucketUuid}", service="api.controller.credentials_bucket")
 * @ParamConverter("bucket", converter="bucket", options={"uuid"="bucketUuid"})
 * @Security("is_granted('ACCESS', bucket)")
 */
class CredentialsBucketController
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param BucketRepository $bucketRepository
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(BucketRepository $bucketRepository, ValidatorInterface $validator, SerializerInterface $serializer, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->validator = $validator;
        $this->bucketRepository = $bucketRepository;
        $this->serializer = $serializer;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @Route("", methods={"GET"})
     * @View
     */
    public function getAction(Bucket $bucket)
    {
        return $bucket;
    }

    /**
     * @Route("/docker-registries", methods={"GET"})
     * @View
     */
    public function listDockerRegistriesAction(Bucket $bucket)
    {
        return $bucket->getDockerRegistries();
    }

    /**
     * @Route("/docker-registries", methods={"POST"})
     * @ParamConverter("credentials", converter="fos_rest.request_body")
     * @View(statusCode=201)
     */
    public function createDockerRegistryAction(Bucket $bucket, DockerRegistry $credentials)
    {
        $violations = $this->validator->validate($credentials);
        if (count($violations) > 0) {
            return FOSRestView::create($violations, 400);
        }

        // basic protection against adding same server address twice
        foreach ($bucket->getDockerRegistries() as $dockerRegistry) {
            if ($dockerRegistry->getServerAddress() == $credentials->getServerAddress()) {
                return FOSRestView::create(new ConstraintViolationList([
                    new ConstraintViolation(
                        'A credential with this registry type/address already exists in this project',
                        'The credential for registry {serverAddress} already exists in this project',
                        [
                            'serverAddress' => $credentials->getServerAddress(),
                        ],
                        $bucket,
                        'serverAddress',
                        $credentials->getServerAddress()
                    ),
                ]), 400);
            }
        }

        $bucket->getDockerRegistries()->add($credentials);

        $this->bucketRepository->save($bucket);
    }

    /**
     * @Route("/docker-registries/{address}", methods={"DELETE"}, requirements={"address"=".+"})
     * @View
     */
    public function deleteDockerRegistryAction(Bucket $bucket, $address)
    {
        if (null === ($registry = $this->getRegistryWithAddress($bucket, $address))) {
            throw new NotFoundHttpException(sprintf('Registry "%s" not found', $address));
        }

        $bucket->getDockerRegistries()->removeElement($registry);
        $this->bucketRepository->save($bucket);
    }

    /**
     * @Route("/docker-registries/{address}", methods={"PATCH"}, requirements={"address"=".+"})
     * @View
     */
    public function patchDockerRegistryAction(Bucket $bucket, string $address, Request $request)
    {
        if (null === ($registry = $this->getRegistryWithAddress($bucket, $address))) {
            throw new NotFoundHttpException(sprintf('Registry "%s" not found', $address));
        }

        $updatedRegistry = $this->applyPatch($registry, \GuzzleHttp\json_decode($request->getContent(), true));

        $bucket->getDockerRegistries()->removeElement($registry);
        $bucket->getDockerRegistries()->add($updatedRegistry);

        $this->bucketRepository->save($bucket);

        return $updatedRegistry;
    }

    /**
     * @Route("/github-tokens", methods={"GET"})
     * @View
     */
    public function listGitHubTokensAction(Bucket $bucket)
    {
        return $bucket->getGitHubTokens();
    }

    /**
     * @Route("/github-tokens", methods={"POST"})
     * @ParamConverter("token", converter="fos_rest.request_body")
     * @View(statusCode=201)
     */
    public function createGitHubTokenAction(Bucket $bucket, GitHubToken $token)
    {
        $violations = $this->validator->validate($token);
        if (count($violations) > 0) {
            return FOSRestView::create($violations, 400);
        }

        $bucket->getGitHubTokens()->add($token);

        $this->bucketRepository->save($bucket);

        return $token;
    }

    /**
     * @Route("/github-tokens/{identifier}", methods={"DELETE"})
     * @View
     */
    public function deleteGitHubTokenAction(Bucket $bucket, $identifier)
    {
        $tokens = $bucket->getGitHubTokens();
        $matchingTokens = $tokens->filter(function (GitHubToken $token) use ($identifier) {
            return $token->getIdentifier() == $identifier;
        });

        foreach ($matchingTokens as $token) {
            $tokens->removeElement($token);
        }

        $this->bucketRepository->save($bucket);
    }

    /**
     * @Route("/clusters", methods={"GET"})
     * @View
     */
    public function listClustersAction(Bucket $bucket)
    {
        return $bucket->getClusters();
    }

    /**
     * @Route("/clusters", methods={"POST"})
     * @ParamConverter("cluster", converter="fos_rest.request_body")
     * @View(statusCode=201)
     */
    public function createClusterAction(Bucket $bucket, Cluster $cluster)
    {
        if (null !== $this->getClusterIdentified($bucket, $cluster->getIdentifier())) {
            $violations = new ConstraintViolationList([
                new ConstraintViolation(
                    'A cluster with this identifier already exists in this team',
                    'The cluster {identifier} already exists in this team',
                    [
                        'identifier' => $cluster->getIdentifier(),
                    ],
                    $bucket,
                    'identifier',
                    $cluster->getIdentifier()
                ),
            ]);
        } else {
            $violations = $this->validator->validate($cluster);
        }

        if (count($violations) > 0) {
            return FOSRestView::create($violations, 400);
        }

        $bucket->getClusters()->add($cluster);

        $this->bucketRepository->save($bucket);

        return $cluster;
    }

    /**
     * @Route("/clusters/{clusterIdentifier}", methods={"PATCH"})
     * @View
     */
    public function patchClusterAction(Bucket $bucket, string $clusterIdentifier, Request $request)
    {
        if (null === ($cluster = $this->getClusterIdentified($bucket, $clusterIdentifier))) {
            throw new NotFoundHttpException(sprintf('Cluster "%s" not found', $clusterIdentifier));
        }

        if (!$this->authorizationChecker->isGranted('EDIT', $cluster)) {
            return new JsonResponse([
                'error' => 'You cannot update the policies of a managed cluster'
            ], 403);
        }

        $updatedCluster = $this->applyPatch($cluster, \GuzzleHttp\json_decode($request->getContent(), true));

        $bucket->getClusters()->removeElement($cluster);
        $bucket->getClusters()->add($updatedCluster);

        $this->bucketRepository->save($bucket);

        return $updatedCluster;
    }

    /**
     * @Route("/clusters/{identifier}", methods={"DELETE"})
     * @View
     */
    public function deleteClusterAction(Bucket $bucket, $identifier)
    {
        $clusters = $bucket->getClusters();
        $matchingClusters = $clusters->filter(function (Cluster $cluster) use ($identifier) {
            return $cluster->getIdentifier() == $identifier;
        });

        foreach ($matchingClusters as $cluster) {
            $clusters->removeElement($cluster);
        }

        $this->bucketRepository->save($bucket);
    }

    /**
     * @param Bucket $bucket
     * @param string $clusterIdentifier
     *
     * @return Cluster|null
     */
    private function getClusterIdentified(Bucket $bucket, string $clusterIdentifier)
    {
        foreach ($bucket->getClusters() as $cluster) {
            if ($cluster->getIdentifier() == $clusterIdentifier) {
                return $cluster;
            }
        }

        return null;
    }

    private function applyPatch($object, array $patch)
    {
        $serializationContext = SerializationContext::create();
        $serializationContext->setAttribute('should-obfuscate', false);
        $serializationContext->setGroups(['Default', 'All']);

        $objectAsArray = \GuzzleHttp\json_decode($this->serializer->serialize($object, 'json', $serializationContext), true);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($patch as $key => $value) {
            $propertyAccessor->setValue($objectAsArray, '['.$key.']', $value);
        }

        $deserializationContext = DeserializationContext::create();
        $deserializationContext->setGroups(['Default', 'All']);

        return $this->serializer->deserialize(
            \GuzzleHttp\json_encode($objectAsArray),
            get_class($object),
            'json',
            $deserializationContext
        );
    }

    private function getRegistryWithAddress(Bucket $bucket, string $address)
    {
        foreach ($bucket->getDockerRegistries() as $registry) {
            if ($registry->getFullAddress() == $address) {
                return $registry;
            }
        }

        foreach ($bucket->getDockerRegistries() as $registry) {
            if ($registry->getServerAddress() == $address) {
                return $registry;
            }
        }

        return null;
    }
}
