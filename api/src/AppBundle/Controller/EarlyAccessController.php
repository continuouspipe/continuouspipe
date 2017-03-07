<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\EarlyAccessCodeType;
use ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessCode;
use ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessCodeRepository;
use ContinuousPipe\Authenticator\EarlyAccess\BypassWhiteListToggleFactory;
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
     * @var BypassWhiteListToggleFactory
     */
    private $bypassWhiteListToggleFactory;

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
        BypassWhiteListToggleFactory $bypassWhiteListToggleFactory,
        Router $router,
        FormFactoryInterface $formFactory
    ) {
        $this->earlyAccessCodeRepository = $earlyAccessCodeRepository;
        $this->bypassWhiteListToggleFactory = $bypassWhiteListToggleFactory;
        $this->router = $router;
        $this->formFactory = $formFactory;
    }

    /**
     * @Route("/early-access/", name="show_early_access_page")
     * @Template
     */
    public function showFormAction(Request $request)
    {
        $code = new \AppBundle\Entity\EarlyAccessCode;
        $form = $this->formFactory->create(EarlyAccessCodeType::class, $code);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bypassWhiteListToggle = $this->bypassWhiteListToggleFactory->createFromSession();
            $bypassWhiteListToggle->activateByCode(EarlyAccessCode::fromString($code->code));
            return new RedirectResponse($this->router->generate('hwi_oauth_connect'));
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
