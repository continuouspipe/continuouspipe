<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Request\ParamConverter;

use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use ContinuousPipe\Security\User\SecurityUser;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserParamConverter implements ParamConverterInterface
{
    /**
     * @var SecurityUserRepository
     */
    private $securityUserRepository;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param SecurityUserRepository $securityUserRepository
     * @param TokenStorageInterface  $tokenStorage
     */
    public function __construct(SecurityUserRepository $securityUserRepository, TokenStorageInterface $tokenStorage)
    {
        $this->securityUserRepository = $securityUserRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();
        if (array_key_exists('byUsername', $options)) {
            $username = $request->get($options['byUsername']);
            try {
                $user = $this->securityUserRepository->findOneByUsername($username);
            } catch (UserNotFound $e) {
                throw new NotFoundHttpException($e->getMessage());
            }
        } elseif (array_key_exists('fromSecurityContext', $options)) {
            if (null === ($token = $this->tokenStorage->getToken())) {
                throw new \RuntimeException('No user found in context');
            }
            if (!(($user = $token->getUser()) instanceof UserInterface)) {
                throw new \RuntimeException('No logged-in user');
            }
        } else {
            throw new \RuntimeException('Unknown user param converter strategy');
        }

        if ($user instanceof SecurityUser) {
            $user = $user->getUser();
        }

        $request->attributes->set($configuration->getName(), $user);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() == 'user';
    }
}
