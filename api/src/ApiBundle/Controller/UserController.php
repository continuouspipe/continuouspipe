<?php

namespace ApiBundle\Controller;

use ApiBundle\Request\CreateApiKeyRequest;
use ContinuousPipe\Authenticator\Security\ApiKey\UserApiKey;
use ContinuousPipe\Authenticator\Security\ApiKey\UserByApiKeyRepository;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Debug\Tests\Fixtures\ClassAlias;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(service="api.controller.user")
 */
class UserController
{
    /**
     * @var UserByApiKeyRepository
     */
    private $userByApiKeyRepository;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(UserByApiKeyRepository $userByApiKeyRepository, ValidatorInterface $validator)
    {
        $this->userByApiKeyRepository = $userByApiKeyRepository;
        $this->validator = $validator;
    }

    /**
     * @Route("/user/{username}", methods={"GET"})
     * @ParamConverter("userObject", converter="user", options={"byUsername"="username"})
     * @Security("is_granted('VIEW', userObject)")
     * @View
     */
    public function getByUsernameAction(User $userObject)
    {
        return $userObject;
    }

    /**
     * @Route("/user/{username}/api-keys", methods={"POST"})
     * @ParamConverter("userObject", converter="user", options={"byUsername"="username"})
     * @ParamConverter("request", converter="fos_rest.request_body")
     * @Security("is_granted('ADMIN', userObject)")
     * @View(statusCode=201)
     */
    public function createApiKeyAction(User $userObject, CreateApiKeyRequest $request)
    {
        $violations = $this->validator->validate($request);
        if ($violations->count() > 0) {
            return new JsonResponse([
                'error' => [
                    'message' => $violations->get(0)->getMessage(),
                ],
            ], 400);
        }

        $apiKey = new UserApiKey(
            Uuid::uuid4(),
            $userObject,
            Uuid::uuid4()->toString(),
            new \DateTime(),
            $request->description
        );

        $this->userByApiKeyRepository->save($apiKey);

        return $apiKey;
    }

    /**
     * @Route("/user/{username}/api-keys", methods={"GET"})
     * @ParamConverter("userObject", converter="user", options={"byUsername"="username"})
     * @Security("is_granted('ADMIN', userObject)")
     * @View
     */
    public function listApiKeysAction(User $userObject)
    {
        return $this->userByApiKeyRepository->findByUser($userObject->getUsername());
    }
}
