<?php

namespace AdminBundle\Controller;

use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="admin.controller.last_tides")
 */
class LastTidesController
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @param TideRepository     $tideRepository
     * @param string             $logStreamUrl
     */
    public function __construct(TideRepository $tideRepository, PaginatorInterface $paginator)
    {
        $this->tideRepository = $tideRepository;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/last-tides", name="admin_last_tides")
     * @Template
     */
    public function listAction(Request $request)
    {
        return [
            'pagination' => $this->paginator->paginate(
                $this->tideRepository->findAll(),
                $request->query->getInt('page', 1),
                50
            ),
        ];
    }
}
