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
            $this->updateTideView($message->getTideUuid());
        }

        $next($message);
    }

    /**
     * @param UuidInterface $uuid
     */
    private function updateTideView(UuidInterface $uuid)
    {
        $this->getTideViewStorage()->save(
            $this->getTideViewFactory()->create($uuid)
        );
    }

    private function getTideViewFactory() : TideViewFactory
    {
        return $this->container->get('river.view.tide_view_factory');
    }

    private function getTideViewStorage() : TideViewStorage
    {
        return $this->container->get('river.view.storage');
    }
}
