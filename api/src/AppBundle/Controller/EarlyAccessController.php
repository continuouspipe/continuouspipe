<?php

namespace AppBundle\Controller;

use AppBundle\Entity\EarlyAccessCode;
use AppBundle\Form\Type\EarlyAccessCodeType;
use ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessToggle;
use ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessCodeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    public function __construct(
        EarlyAccessCodeRepository $earlyAccessCodeRepository,
        EarlyAccessToggle $earlyAccessToggle,
        Router $router,
        FormFactoryInterface $formFactory
    ) {
        $this->earlyAccessCodeRepository = $earlyAccessCodeRepository;
        $this->earlyAccessToggle = $earlyAccessToggle;
        $this->router = $router;
        $this->formFactory = $formFactory;
    }

    /**
     * @Route("/early-access/", name="show_early_access_page")
     * @Template
     */
    public function showFormAction(Request $request)
    {
        $code = new EarlyAccessCode;
        $form = $this->formFactory->create(EarlyAccessCodeType::class, $code);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->earlyAccessToggle->activate();
            return new RedirectResponse($this->router->generate('hwi_oauth_connect'));
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
