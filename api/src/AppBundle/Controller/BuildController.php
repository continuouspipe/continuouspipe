<?php

namespace AppBundle\Controller;

use Builder\DockerBuilder;
use Builder\Request\BuildRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="app.controller.build")
 */
class BuildController
{
    /**
     * @var DockerBuilder
     */
    private $builder;

    /**
     * @param DockerBuilder $builder
     */
    public function __construct(DockerBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @Route("/build", methods={"POST"})
     * @ParamConverter("request", converter="build_request")
     */
    public function buildAction(BuildRequest $request)
    {
        $this->builder->build($request->getRepository(), $request->getImage());

        return new Response('OK');
    }
}
