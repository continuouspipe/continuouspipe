<?php

namespace ContinuousPipe\River\View\EventBusMiddleware;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\View\Factory\TideViewFactory;
use ContinuousPipe\River\View\Storage\TideViewStorage;
use Ramsey\Uuid\UuidInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UpdateTideViewWhenTideEventPublished implements MessageBusMiddleware
{
    /**
     * @var TideViewStorage
     */
    private $tideViewStorage;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param TideViewStorage $tideViewStorage
     * @param ContainerInterface $container
     */
    public function __construct(TideViewStorage $tideViewStorage, ContainerInterface $container)
    {
        $this->tideViewStorage = $tideViewStorage;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        if ($message instanceof TideEvent) {
            $this->updateTideView($message->getTideUuid());
        }

        $next($message);
    }

    /**
     * @param UuidInterface $uuid
     */
    private function updateTideView(UuidInterface $uuid)
    {
        $this->tideViewStorage->save(
            $this->getTideViewFactory()->create($uuid)
        );
    }

    /**
     * This has been added because of a circular dependency with the TideFactory. Once the `Tide`
     * object will have been refactored to have dependencies coming as method arguments, that should
     * be a LOT better.
     *
     * @return TideViewFactory
     */
    private function getTideViewFactory()
    {
        return $this->container->get('river.view.tide_view_factory');
    }
}
