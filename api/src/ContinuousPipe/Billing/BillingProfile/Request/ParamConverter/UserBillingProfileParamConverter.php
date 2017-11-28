<?php

namespace ContinuousPipe\Billing\BillingProfile\Request\ParamConverter;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserBillingProfileParamConverter implements ParamConverterInterface
{
    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;

    public function __construct(UserBillingProfileRepository $userBillingProfileRepository)
    {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = array_merge(['uuid' => 'uuid'], $configuration->getOptions());
        if (null === ($uuid = $request->get($options['uuid']))) {
            throw new HttpException(500, sprintf('No uuid found in field "%s"', $options['uuid']));
        }

        try {
            $billingProfile = $this->userBillingProfileRepository->find(Uuid::fromString($uuid));
        } catch (UserBillingProfileNotFound $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $request->attributes->set($configuration->getName(), $billingProfile);

        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() == 'billingProfile';
    }
}
