<?php

namespace LogStream;

class HookableLoggerFactory implements LoggerFactory
{
    /**
     * @var LoggerFactory
     */
    private $decoratedLogger;

    /**
     * @var callable|null
     */
    private $updateHook;

    /**
     * @param LoggerFactory $decoratedLogger
     */
    public function __construct(LoggerFactory $decoratedLogger)
    {
        $this->decoratedLogger = $decoratedLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return new HookableLogger(
            $this->decoratedLogger->create(),
            $this->updateHook
        );
    }

    /**
     * {@inheritdoc}
     */
    public function from(Log $log)
    {
        return new HookableLogger(
            $this->decoratedLogger->from($log),
            $this->updateHook
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fromId($identifier)
    {
        return new HookableLogger(
            $this->decoratedLogger->fromId($identifier),
            $this->updateHook
        );
    }

    /**
     * @param callable|null $updateHook
     */
    public function setUpdateHook($updateHook)
    {
        $this->updateHook = $updateHook;
    }
}
