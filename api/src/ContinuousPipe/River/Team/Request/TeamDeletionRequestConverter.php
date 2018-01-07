<?php

namespace ContinuousPipe\River\Team\Request;

use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\Security\Request\ParamConverter\TeamParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class TeamDeletionRequestConverter implements ParamConverterInterface
{
    /**
     * @var TeamParamConverter
     */
    private $teamParamConverter;

    /**
     * @var FlatFlowRepository
     */
    private $flowRepository;

    public function __construct(TeamParamConverter $teamParamConverter, FlatFlowRepository $flowRepository)
    {
        $this->flowRepository = $flowRepository;
        $this->teamParamConverter = $teamParamConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $teamParamConverterConfig = clone $configuration;
        $teamParamConverterConfig->setName('team');
        $this->teamParamConverter->apply($request, $teamParamConverterConfig);

        $teamDeletionRequest = new TeamDeletionRequest($request->attributes->get('team'), $this->flowRepository);

        $request->attributes->set($configuration->getName(), $teamDeletionRequest);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return 'teamDeletionRequest' === $configuration->getConverter();
    }
}
