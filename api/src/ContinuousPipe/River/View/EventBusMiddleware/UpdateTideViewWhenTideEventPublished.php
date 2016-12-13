<?php

namespace ContinuousPipe\River\View\EventBusMiddleware;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\View\Factory\TideViewFactory;
use ContinuousPipe\River\View\Storage\TideViewStorage;
use Ramsey\Uuid\UuidInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class UpdateTideViewWhenTideEventPublished implements MessageBusMiddleware
{
    /**
     * @var TideViewFactory
     */
    private $tideViewFactory;

    /**
     * @var \ContinuousPipe\River\View\Storage\TideViewStorage
     */
    private $tideViewStorage;

    /**
     * @param TideViewStorage $tideViewStorage
     * @param TideViewFactory $tideViewFactory
     */
    public function __construct(TideViewStorage $tideViewStorage, TideViewFactory $tideViewFactory)
    {
        $this->tideViewFactory = $tideViewFactory;
        $this->tideViewStorage = $tideViewStorage;
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
            $this->tideViewFactory->create($uuid)
        );
    }
}
