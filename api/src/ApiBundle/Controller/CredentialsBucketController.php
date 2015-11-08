<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use ContinuousPipe\Security\Credentials\GitHubToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
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
     * @param BucketRepository   $bucketRepository
     * @param ValidatorInterface $validator
     */
    public function __construct(BucketRepository $bucketRepository, ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->bucketRepository = $bucketRepository;
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

        $bucket->getDockerRegistries()->add($credentials);

        $this->bucketRepository->save($bucket);
    }

    /**
     * @Route("/docker-registries/{serverAddress}", methods={"DELETE"})
     * @View
     */
    public function deleteDockerRegistryAction(Bucket $bucket, $serverAddress)
    {
        $registries = $bucket->getDockerRegistries();
        $matchingRegistries = $registries->filter(function (DockerRegistry $dockerRegistry) use ($serverAddress) {
            return $dockerRegistry->getServerAddress() == $serverAddress;
        });

        foreach ($matchingRegistries as $registry) {
            $registries->removeElement($registry);
        }

        $this->bucketRepository->save($bucket);
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
     * @Route("/github-tokens/{login}", methods={"DELETE"})
     * @View
     */
    public function deleteGitHubTokenAction(Bucket $bucket, $login)
    {
        $tokens = $bucket->getGitHubTokens();
        $matchingTokens = $tokens->filter(function (GitHubToken $token) use ($login) {
            return $token->getLogin() == $login;
        });

        foreach ($matchingTokens as $token) {
            $tokens->removeElement($token);
        }

        $this->bucketRepository->save($bucket);
    }
}
