<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Request\ParamConverter;

use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * @param TokenStorageInterface $tokenStorage
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
        if (array_key_exists('byEmail', $options)) {
            $email = $request->get($options['byEmail']);
            $securityUser = $this->securityUserRepository->findOneByEmail($email);
        } else if (null !== ($token = $this->tokenStorage->getToken())) {
            if (null === ($securityUser = $token->getUser())) {
                throw new \RuntimeException('No logged-in user');
            }
        } else {
            throw new \RuntimeException('No user found in context');
        }

        $user = $securityUser->getUser();
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