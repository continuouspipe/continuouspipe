<?php

namespace ContinuousPipe\River\EventBus;

use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class FinishesHandlingMessageBeforeHandlingNext implements MessageBusMiddleware
{
    /**
     * @var array
     */
    private $queue = [];

    /**
     * @var bool
     */
    private $isHandling = false;

    /**
     * Completely finishes handling the current message, before allowing other middlewares to start handling new
     * messages.
     *
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        $this->queue[] = [$next, $message];

        if (!$this->isHandling) {
            $this->isHandling = true;

            while ($queuedMessage = array_shift($this->queue)) {
                try {
                    $closure = $queuedMessage[0];
                    $closure($queuedMessage[1]);
                } catch (\Exception $exception) {
                    $this->isHandling = false;

                    throw $exception;
                }
            }

            $this->isHandling = false;
        }
    }
}
