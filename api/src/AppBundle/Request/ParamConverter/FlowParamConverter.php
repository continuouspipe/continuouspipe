<?php

namespace AppBundle\Request\ParamConverter;

use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @param FlowRepository     $flowRepository
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
        try {
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
        } catch (FlowNotFound $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() == 'flow';
    }
}
