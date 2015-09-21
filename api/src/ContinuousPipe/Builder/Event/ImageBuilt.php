<?php

namespace ContinuousPipe\Builder\Event;

use ContinuousPipe\Builder\Build;
use LogStream\Logger;

class ImageBuilt
{
    /**
     * @var Build
     */
    private $build;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Build  $build
     * @param Logger $logger
     */
    public function __construct(Build $build, Logger $logger)
    {
        $this->build = $build;
        $this->logger = $logger;
    }

    /**
     * @return Build
     */
    public function getBuild()
    {
        return $this->build;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
