<?php

namespace ApiBundle\Controller;

use AppBundle\Security\User\SecurityUserRepository;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="api.controller.user")
 */
class UserController
{
    /**
     * @var SecurityUserRepository
     */
    private $userRepository;

    /**
     * @param SecurityUserRepository $userRepository
     */
    public function __construct(SecurityUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/user/{email}", methods={"GET"})
     * @View
     */
    public function getByEmailAction($email)
    {
        $securityUser = $this->userRepository->findOneByEmail($email);

        return $securityUser->getUser();
    }
}
