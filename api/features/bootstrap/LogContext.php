<?php

use Behat\Behat\Context\Context;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class LogContext implements Context
{
    /**
     * @var DebugLoggerInterface
     */
    private $debugLogger;

    public function __construct(DebugLoggerInterface $debugLogger)
    {
        $this->debugLogger = $debugLogger;
    }

    /**
     * @Given I should see the :message message in the log
     */
    public function iShouldSeeTheWarningMessageInTheLog($message)
    {
        foreach ($this->debugLogger->getLogs() as $logEntry) {
            if ($message === $logEntry['message']) {
                return;
            }
        }

        throw new \RuntimeException('The specified message not found in the log.');
    }
}
