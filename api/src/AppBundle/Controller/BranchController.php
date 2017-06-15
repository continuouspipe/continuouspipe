<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\Branch;
use ContinuousPipe\River\Command\PinBranch;
use ContinuousPipe\River\Command\UnpinBranch;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\View\Storage\BranchViewStorage;
use ContinuousPipe\River\View\Storage\PullRequestViewStorage;
use FOS\RestBundle\Controller\Annotations\View;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SimpleBus\Message\Bus\MessageBus;

/**
 * @Route(service="app.controller.branch")
 */
class BranchController
{
    /**
     * @var MessageBus
     */
    private $commandBus;
    /**
     * @var BranchViewStorage
     */
    private $branchViewStorage;
    /**
     * @var PullRequestViewStorage
     */
    private $pullRequestViewStorage;

    public function __construct(MessageBus $commandBus, BranchViewStorage $branchViewStorage, PullRequestViewStorage $pullRequestViewStorage)
    {
        $this->commandBus = $commandBus;
        $this->branchViewStorage = $branchViewStorage;
        $this->pullRequestViewStorage = $pullRequestViewStorage;
    }

    /**
     * @Route("/flows/{uuid}/pinned-branch", methods={"POST"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("branch", converter="fos_rest.request_body")
     * @Security("is_granted('ADMIN', flow)")
     * @View(statusCode=204)
     */
    public function pinAction($uuid, Branch $branch)
    {
        $this->commandBus->handle(new PinBranch(Uuid::fromString($uuid), (string) $branch));
    }

    /**
     * @Route("/flows/{uuid}/pinned-branch", methods={"DELETE"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("branch", converter="fos_rest.request_body")
     * @Security("is_granted('ADMIN', flow)")
     * @View(statusCode=204)
     */
    public function unpinAction($uuid, Branch $branch)
    {
        $this->commandBus->handle(new UnpinBranch(Uuid::fromString($uuid), (string) $branch));
    }

    /**
     * @Route("/flows/{uuid}/branches/refresh", methods={"POST"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @View(statusCode=204)
     */
    public function refreshAction($uuid, FlatFlow $flow)
    {
        $this->branchViewStorage->save($uuid);
        $this->pullRequestViewStorage->save($uuid, $flow->getRepository());
    }
}
