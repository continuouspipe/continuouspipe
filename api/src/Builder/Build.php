<?php

namespace Builder;

use Builder\Request\BuildRequest;
use Rhumsaa\Uuid\Uuid;

class Build
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var BuildRequest
     */
    private $request;

    private function __construct()
    {
    }

    /**
     * @param BuildRequest $request
     *
     * @return Build
     */
    public static function fromRequest(BuildRequest $request)
    {
        $build = new self();
        $build->uuid = Uuid::uuid1();
        $build->request = $request;

        return $build;
    }
}
