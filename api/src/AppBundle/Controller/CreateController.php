<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CreateController extends Controller
{
    /**
     * @Route("/create/from-compose", methods={"POST"})
     */
    public function createFromComposeAction(Request $request)
    {

    }
}
