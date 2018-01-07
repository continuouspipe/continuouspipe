<?php

namespace AppBundle\Request\ParamConverter;

use ContinuousPipe\River\Repository\CodeRepositoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class CodeRepositoryParamConverter implements ParamConverterInterface
{
    /**
     * @var CodeRepositoryRepository
     */
    private $codeRepositoryRepository;

    /**
     * @param CodeRepositoryRepository $codeRepositoryRepository
     */
    public function __construct(CodeRepositoryRepository $codeRepositoryRepository)
    {
        $this->codeRepositoryRepository = $codeRepositoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $identifierKey = $configuration->getOptions()['identifier'];
        $identifier = $request->get($identifierKey);
        $codeRepository = $this->codeRepositoryRepository->findByIdentifier($identifier);
        $request->attributes->set($configuration->getName(), $codeRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() == 'code-repository';
    }
}
