<?php

namespace ContinuousPipe\River\EventBus;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\TideSaga;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TideSagaApplyMiddleware implements MessageBusMiddleware
{
    /**
     * @var TideSaga
     */
    private $tideSaga;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        if ($message instanceof TideEvent) {
            $this->getTideSaga()->notify($message);
        }

        $next($message);
    }

    /**
     * @return TideSaga
     */
    private function getTideSaga()
    {
        if (null === $this->tideSaga) {
            $this->tideSaga = $this->container->get('river.tide.saga');
        }

        return $this->tideSaga;
    }
}
