<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Flex\AsFeature\Command\ActivateFlex;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SimpleBus\Message\Bus\MessageBus;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.flex")
 */
class FlowFlexController
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param MessageBus $commandBus
     */
    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
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
