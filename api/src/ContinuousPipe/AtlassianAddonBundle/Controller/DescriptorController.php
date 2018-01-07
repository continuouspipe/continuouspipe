<?php

namespace ContinuousPipe\AtlassianAddonBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DescriptorController extends Controller
{
    /**
     * @Route("", name="atlassian_addon_descriptor")
     * @Template
     */
    public function getAction()
    {
        return [];
    }
}
