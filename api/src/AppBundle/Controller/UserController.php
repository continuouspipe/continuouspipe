<?php

namespace AppBundle\Controller;

use AppBundle\Security\User\SecurityUserRepository;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="app.controller.user")
 */
class UserController
{
    /**
     * @var SecurityUserRepository
     */
    private $userRepository;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SecurityUserRepository $userRepository, SerializerInterface $serializer)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/user/{email}", methods={"GET"})
     */
    public function getByEmailAction($email)
    {
        $securityUser = $this->userRepository->findOneByEmail($email);
        $user = $securityUser->getUser();

        return new Response($this->serializer->serialize($user, 'json'), 200, [
            'Content-Type' => 'application/json',
        ]);
    }
}
