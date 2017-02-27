<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Security\ApiKey\UserApiKeyRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route(service="api.controller.api_key")
 */
class ApiKeyController
{
    /**
     * @var UserApiKeyRepository
     */
    private $userApiKeyRepository;

    public function __construct(UserApiKeyRepository $userApiKeyRepository)
    {
        $this->userApiKeyRepository = $userApiKeyRepository;
    }

    /**
     * @Route("/api-key/{key}/user", methods={"GET"})
     * @Security("is_granted('ROLE_SYSTEM')")
     * @View
     */
    public function lookupUserAction(string $key)
    {
        if (null === ($user = $this->userApiKeyRepository->findUserByApiKey($key))) {
            throw new NotFoundHttpException(sprintf('No user found behind the API key "%s"', $key));
        }

        return $user;
    }
}
