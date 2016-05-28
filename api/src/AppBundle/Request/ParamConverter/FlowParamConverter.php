<?php

namespace AppBundle\Request\ParamConverter;

use ContinuousPipe\River\Repository\FlowRepository;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class FlowParamConverter implements ParamConverterInterface
{
    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param FlowRepository $flowRepository
     */
    public function __construct(FlowRepository $flowRepository)
    {
        $this->flowRepository = $flowRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $identifierKey = $configuration->getOptions()['identifier'];
        $identifier = $request->get($identifierKey);

        $uuid = Uuid::fromString($identifier);
        $flow = $this->flowRepository->find($uuid);

        $request->attributes->set($configuration->getName(), $flow);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() == 'flow';
    }
}
