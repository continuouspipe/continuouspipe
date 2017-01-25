<?php

namespace ContinuousPipe\River\Tide\Concurrency\Lock;

use ContinuousPipe\Worker\TemporaryException;

class LockerException extends \Exception implements TemporaryException
{
}
