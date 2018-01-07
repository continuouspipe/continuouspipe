<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Flex\AsFeature\Command\ActivateFlex;
use ContinuousPipe\River\Flex\FlexAvailabilityDetector;
use ContinuousPipe\River\Flex\FlexException;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SimpleBus\Message\Bus\MessageBus;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.features")
 */
class FlowFeaturesController
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var FlexAvailabilityDetector
     */
    private $flexAvailabilityDetector;

    public function __construct(MessageBus $commandBus, FlexAvailabilityDetector $flexAvailabilityDetector)
    {
        $this->commandBus = $commandBus;
        $this->flexAvailabilityDetector = $flexAvailabilityDetector;
    }

    /**
     * @Route("/flows/{uuid}/features", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function listAction(FlatFlow $flow)
    {
        $flexFeature = [
            'feature' => 'flex',
            'enabled' => $flow->isFlex(),
        ];

        try {
            $flexFeature['available'] = $this->flexAvailabilityDetector->isFlexAvailable($flow);
        } catch (FlexException $e) {
            $flexFeature['available'] = false;
            $flexFeature['reason'] = $e->getMessage();
        }

        return [
            $flexFeature,
        ];
    }

    /**
     * @Route("/flows/{uuid}/features/flex", methods={"POST"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('UPDATE', flow)")
     * @View
     */
    public function activateFlexAction(FlatFlow $flow)
    {
        $this->commandBus->handle(new ActivateFlex(
            $flow->getUuid()
        ));
    }
}
