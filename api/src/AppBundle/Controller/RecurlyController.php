<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/account/recurly")
 */
class RecurlyController extends Controller
{
    /**
     * @Route("/success")
     */
    public function successAction(Request $request)
    {
        $request->getSession()->getFlashBag()->add('success', 'You\'ve been successfully subscribed');

        return new RedirectResponse($this->generateUrl('account_billing_profile'));
    }
}
