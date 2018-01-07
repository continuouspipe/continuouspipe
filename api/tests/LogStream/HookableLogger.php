<?php

namespace LogStream;

use LogStream\Node\Node;

class HookableLogger implements Logger
{
    /**
     * @var Logger
     */
    private $decoratedLogger;

    /**
     * @var callable|null
     */
    private $updateHook;

    public function __construct(Logger $decoratedLogger, callable $updateHook = null)
    {
        $this->decoratedLogger = $decoratedLogger;
        $this->updateHook = $updateHook;
    }

    /**
     * {@inheritdoc}
     */
    public function child(Node $node)
    {
        return new HookableLogger(
            $this->decoratedLogger->child($node),
            $this->updateHook
        );
    }

    /**
     * {@inheritdoc}
     */
    public function update(Node $node)
    {
        if (null !== ($updateHook = $this->updateHook)) {
            return $updateHook($node, $this->decoratedLogger);
        }

        return new HookableLogger(
            $this->decoratedLogger->update($node),
            $this->updateHook
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatus($status)
    {
        return new HookableLogger(
            $this->decoratedLogger->updateStatus($status),
            $this->updateHook
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLog()
    {
        return $this->decoratedLogger->getLog();
    }
}
