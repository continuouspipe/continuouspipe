<?php

namespace ContinuousPipe\Security\Request\ParamConverter;

use ContinuousPipe\Security\Account\AccountNotFound;
use ContinuousPipe\Security\Account\AccountRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AccountParamConverter implements ParamConverterInterface
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var string
     */
    private $converterName;

    public function __construct(AccountRepository $accountRepository, string $converterName = 'account')
    {
        $this->accountRepository = $accountRepository;
        $this->converterName = $converterName;
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
            $bucket = $this->accountRepository->find($uuid);
        } catch (AccountNotFound $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $request->attributes->set($configuration->getName(), $bucket);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() == $this->converterName;
    }
}
