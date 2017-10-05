<?php

namespace ContinuousPipe\Pipe\Promise;

use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\Timer;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

class PromiseBuilder
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var PromiseInterface|null
     */
    private $promise;

    /**
     * @var callable[]
     */
    private $wrappers = [];

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * Retry the given callable each given internal. Will try until the deferred
     * is resolved.
     *
     * @param int      $interval
     * @param callable $callable
     *
     * @return PromiseBuilder
     */
    public function retry($interval, callable $callable)
    {
        $deferred = new Deferred();

        // Each, $interval second, call the callable
        $timer = $this->loop->addPeriodicTimer($interval, function (Timer $timer) use ($deferred, $callable) {
            $callable($deferred, $timer);
        });

        $this->promise = $deferred->promise();
        $this->wrappers[] = function (Promise $promise) use ($timer) {
            $promise->always(function () use ($timer) {
                $timer->cancel();
            });

            return $promise;
        };

        return $this;
    }

    /**
     * Add a timeout around the given promise.
     *
     * @param int $timeout
     *
     * @return PromiseBuilder
     */
    public function withTimeout($timeout)
    {
        $this->promise = \React\Promise\Timer\timeout($this->promise, $timeout, $this->loop);

        return $this;
    }

    /**
     * @return PromiseInterface
     */
    public function getPromise()
    {
        $promise = $this->promise;
        foreach ($this->wrappers as $wrapper) {
            $promise = $wrapper($promise);
        }

        return $promise;
    }
}
