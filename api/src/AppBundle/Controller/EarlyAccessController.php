<?php

namespace AppBundle\Controller;

use ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessToggle;
use ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessCodeNotFoundException;
use ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessCodeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/", service="app.controller.early_access")
 */
class EarlyAccessController
{
    /**
     * @var EarlyAccessCodeRepository
     */
    private $earlyAccessCodeRepository;

    /**
     * @var EarlyAccessToggle
     */
    private $earlyAccessToggle;

    /**
     * @var Router
     */
    private $router;

    public function __construct(
        EarlyAccessCodeRepository $earlyAccessCodeRepository,
        EarlyAccessToggle $earlyAccessToggle,
        Router $router
    ) {
        $this->earlyAccessCodeRepository = $earlyAccessCodeRepository;
        $this->earlyAccessToggle = $earlyAccessToggle;
        $this->router = $router;
    }

    /**
     * @Route("/early-access/{code}/enter", name="enter_early_access_code", methods={"POST"})
     */
    public function enterAction($code)
    {
        try {
            $this->earlyAccessCodeRepository->findByCode($code);
            $this->earlyAccessToggle->activate();
        } catch (EarlyAccessCodeNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return new RedirectResponse($this->router->generate('hwi_oauth_connect'));
    }
}
