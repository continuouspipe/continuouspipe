<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController
{
    /**
     * @Route("/", methods={"GET"}, name="home")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }
}
