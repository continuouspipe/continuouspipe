<?php

namespace AppBundle\Controller;

use AppBundle\Request\CreateApiKeyRequest;
use ContinuousPipe\Authenticator\Security\ApiKey\UserApiKeyFactory;
use ContinuousPipe\Security\ApiKey\UserApiKey;
use ContinuousPipe\Security\ApiKey\UserApiKeyRepository;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Debug\Tests\Fixtures\ClassAlias;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(service="api.controller.user")
 */
class UserController
{
    /**
     * @var \ContinuousPipe\Security\ApiKey\UserApiKeyRepository
     */
    private $userByApiKeyRepository;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var UserApiKeyFactory
     */
    private $userApiKeyFactory;

    public function __construct(
        UserApiKeyRepository $userByApiKeyRepository,
        ValidatorInterface $validator,
        UserApiKeyFactory $userApiKeyFactory
    ) {
        $this->userByApiKeyRepository = $userByApiKeyRepository;
        $this->validator = $validator;
        $this->userApiKeyFactory = $userApiKeyFactory;
    }

    /**
     * @Route("/user/{username}", methods={"GET"})
     * @ParamConverter("userObject", converter="authenticator_user", options={"byUsername"="username"})
     * @Security("is_granted('VIEW', userObject)")
     * @View
     */
    public function getByUsernameAction(User $userObject)
    {
        return $userObject;
    }

    /**
     * @Route("/user/{username}/api-keys", methods={"POST"})
     * @ParamConverter("userObject", converter="authenticator_user", options={"byUsername"="username"})
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

        return $this->userApiKeyFactory->create($userObject, $request->description);
    }

    /**
     * @Route("/user/{username}/api-keys", methods={"GET"})
     * @ParamConverter("userObject", converter="authenticator_user", options={"byUsername"="username"})
     * @Security("is_granted('ADMIN', userObject)")
     * @View
     */
    public function listApiKeysAction(User $userObject)
    {
        return $this->userByApiKeyRepository->findByUser($userObject->getUsername());
    }

    /**
     * @Route("/user/{username}/api-keys/{apiKey}", methods={"DELETE"})
     * @ParamConverter("userObject", converter="authenticator_user", options={"byUsername"="username"})
     * @Security("is_granted('ADMIN', userObject)")
     * @View
     */
    public function deleteApiKeyAction(User $userObject, string $apiKey)
    {
        if (null === ($key = $this->findApiKey($userObject, $apiKey))) {
            throw new NotFoundHttpException(sprintf('API key "%s" not found', $apiKey));
        }

        $this->userByApiKeyRepository->delete($userObject->getUsername(), $key->getUuid());
    }

    /**
     * @param User $user
     * @param string $key
     *
     * @return UserApiKey|null
     */
    private function findApiKey(User $user, string $key)
    {
        $keys = $this->userByApiKeyRepository->findByUser($user->getUsername());

        foreach ($keys as $userKey) {
            if ($userKey->getApiKey() == $key) {
                return $userKey;
            }
        }

        return null;
    }
}
