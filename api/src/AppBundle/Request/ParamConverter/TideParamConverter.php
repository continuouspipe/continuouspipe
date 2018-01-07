<?php

namespace AppBundle\Request\ParamConverter;

use ContinuousPipe\River\View\TideRepository;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class TideParamConverter implements ParamConverterInterface
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param TideRepository $tideRepository
     */
    public function __construct(TideRepository $tideRepository)
    {
        $this->tideRepository = $tideRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $identifierKey = $configuration->getOptions()['identifier'];
        $identifier = $request->get($identifierKey);

        $uuid = Uuid::fromString($identifier);
        $tide = $this->tideRepository->find($uuid);

        $request->attributes->set($configuration->getName(), $tide);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() == 'tide';
    }
}
