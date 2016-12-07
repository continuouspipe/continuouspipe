<?php

namespace AppBundle\Request\ParamConverter;

use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
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
     * @var FlatFlowRepository
     */
    private $flatFlowRepository;

    /**
     * @param FlowRepository $flowRepository
     * @param FlatFlowRepository $flatFlowRepository
     */
    public function __construct(FlowRepository $flowRepository, FlatFlowRepository $flatFlowRepository)
    {
        $this->flowRepository = $flowRepository;
        $this->flatFlowRepository = $flatFlowRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();
        $identifierKey = $options['identifier'];
        $identifier = $request->get($identifierKey);

        $uuid = Uuid::fromString($identifier);

        if (isset($options['flat'])) {
            $flow = $this->flatFlowRepository->find($uuid);
        } else {
            $flow = $this->flowRepository->find($uuid);
        }

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
