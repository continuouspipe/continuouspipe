<?php

namespace AdminBundle\Controller;

use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\LogStream\ArchiveLogs\Command\ArchiveFlowLogsCommand;
use ContinuousPipe\River\Managed\Resources\Discrepancies\RepairResourcesDiscrepancies;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Team\Team;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route(service="admin.controller.flow")
 */
class FlowController
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param MessageBus            $commandBus
     * @param UrlGeneratorInterface $urlGenerator
     * @param Session               $session
     */
    public function __construct(MessageBus $commandBus, UrlGeneratorInterface $urlGenerator, Session $session)
    {
        $this->commandBus = $commandBus;
        $this->urlGenerator = $urlGenerator;
        $this->session = $session;
    }

    /**
     * @Route("/teams/{team}/flows/{flow}/archive-logs", methods={"POST"}, name="admin_tides_archive_logs")
     * @ParamConverter("team", converter="team", options={"slug"="team"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="flow", "flat"=true})
     */
    public function archiveLogsAction(Team $team, FlatFlow $flow)
    {
        $this->commandBus->handle(new ArchiveFlowLogsCommand($flow->getUuid()));

        $this->session->getFlashBag()->add('success', 'FlatFlow\'s tides\' logs successfully archived');

        return new RedirectResponse(
            $this->urlGenerator->generate('admin_tides', [
                'team' => $team->getSlug(),
                'flow' => (string) $flow->getUuid(),
            ])
        );
    }

    /**
     * @Route("/teams/{team}/flows/{flow}/repair-resources-discrepancies", methods={"POST"}, name="admin_flow_repair_resources_discrepancies")
     * @ParamConverter("team", converter="team", options={"slug"="team"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="flow", "flat"=true})
     */
    public function repairDiscrepanciesAction(Team $team, FlatFlow $flow, Request $request)
    {
        $this->commandBus->handle(new RepairResourcesDiscrepancies(
            new \DateTime($request->query->get('left', '-15 days')),
            new \DateTime($request->query->get('right', 'now')),
            $flow->getUuid()
        ));

        $this->session->getFlashBag()->add('success', 'Discrepancies successfully repaired');

        return new RedirectResponse(
            $this->urlGenerator->generate('admin_tides', [
                'team' => $team->getSlug(),
                'flow' => (string) $flow->getUuid(),
            ])
        );
    }
}
