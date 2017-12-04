<?php

namespace ContinuousPipe\Authenticator\Request\ParamConverter;

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
     * @var string
     */
    private $converterName;

    public function __construct(SecurityUserRepository $securityUserRepository, TokenStorageInterface $tokenStorage, string $converterName = 'user')
    {
        $this->securityUserRepository = $securityUserRepository;
        $this->tokenStorage = $tokenStorage;
        $this->converterName = $converterName;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();
        if (array_key_exists('byUsername', $options)) {
            $user = $this->findUserByUsername(
                $request->get($options['byUsername'])
            );
        } elseif (array_key_exists('fromSecurityContext', $options)) {
            $user = $this->getUserFromSecurityContext();
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
        return $configuration->getConverter() == $this->converterName;
    }

    /**
     * @param string $usernameOrEmail
     *
     * @throws NotFoundHttpException
     *
     * @return SecurityUser
     */
    private function findUserByUsername($usernameOrEmail)
    {
        try {
            return $this->securityUserRepository->findOneByUsername($usernameOrEmail);
        } catch (UserNotFound $e) {
            try {
                return $this->securityUserRepository->findOneByEmail($usernameOrEmail);
            } catch (UserNotFound $e) {
                throw new NotFoundHttpException(sprintf(
                    'No user matching username or email "%s" found',
                    $usernameOrEmail
                ));
            }
        }
    }

    /**
     * @return UserInterface
     */
    private function getUserFromSecurityContext()
    {
        if (null === ($token = $this->tokenStorage->getToken())) {
            throw new \RuntimeException('No user found in context');
        }

        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            throw new \RuntimeException('No logged-in user');
        }

        return $user;
    }
}
