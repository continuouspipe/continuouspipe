<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/account/recurly")
 */
class RecurlyController
{
    /**
     * @Route("/success")
     */
    public function successAction(Request $request)
    {
        var_dump(
            $request->query->get('account_code'),
            $request->query->get('plan_code')
        );

        return new Response();
    }
}
