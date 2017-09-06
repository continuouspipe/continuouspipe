<?php

use Behat\Behat\Context\Context;
use LogStream\HookableLoggerFactory;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class LogContext implements Context
{
    /**
     * @var DebugLoggerInterface
     */
    private $debugLogger;

    /**
     * @var HookableLoggerFactory
     */
    private $hookableLoggerFactory;

    public function __construct(DebugLoggerInterface $debugLogger, HookableLoggerFactory $hookableLoggerFactory)
    {
        $this->debugLogger = $debugLogger;
        $this->hookableLoggerFactory = $hookableLoggerFactory;
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

    /**
     * @Given the log stream client will fail to update logs
     */
    public function theLogStreamClientWillFailToUpdateLogs()
    {
        $this->hookableLoggerFactory->setUpdateHook(function() {
            throw new \LogStream\Client\ClientException('Found status 0');
        });
    }
}
