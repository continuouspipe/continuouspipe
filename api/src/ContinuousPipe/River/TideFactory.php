<?php

namespace ContinuousPipe\River;

use LogStream\LoggerFactory;
use Rhumsaa\Uuid\Uuid;

class TideFactory
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param Flow          $flow
     * @param CodeReference $codeReference
     *
     * @return Tide
     */
    public function create(Flow $flow, CodeReference $codeReference)
    {
        $log = $this->loggerFactory->create()->getLog();
        $tide = Tide::create(Uuid::uuid1(), $flow, $codeReference, $log);

        return $tide;
    }
}
