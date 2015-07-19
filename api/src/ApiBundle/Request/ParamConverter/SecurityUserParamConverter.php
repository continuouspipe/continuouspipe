<?php

namespace ApiBundle\Request\ParamConverter;

use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class SecurityUserParamConverter implements ParamConverterInterface
{
    /**
     * @var SecurityUserRepository
     */
    private $securityUserRepository;

    /**
     * @param SecurityUserRepository $securityUserRepository
     */
    public function __construct(SecurityUserRepository $securityUserRepository)
    {
        $this->securityUserRepository = $securityUserRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $email = $request->get('email');
        $securityUser = $this->securityUserRepository->findOneByEmail($email);
        $request->attributes->set($configuration->getName(), $securityUser);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() == 'security_user';
    }
}