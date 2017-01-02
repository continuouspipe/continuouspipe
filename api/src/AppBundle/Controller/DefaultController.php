<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController
{
    /**
     * @Route("/", methods={"GET"}, name="default_route")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }
}
