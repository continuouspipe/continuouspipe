<?php

namespace ContinuousPipe\River\View\EventBusMiddleware;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\River\View\Factory\TideViewFactory;
use Ramsey\Uuid\UuidInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class UpdateTideViewWhenTideEventPublished implements MessageBusMiddleware
{
    /**
     * @var TideRepository
     */
    private $tideViewRepository;

    /**
     * @var TideViewFactory
     */
    private $tideViewFactory;

    /**
     * @param TideRepository  $tideViewRepository
     * @param TideViewFactory $tideViewFactory
     */
    public function __construct(TideRepository $tideViewRepository, TideViewFactory $tideViewFactory)
    {
        $this->tideViewRepository = $tideViewRepository;
        $this->tideViewFactory = $tideViewFactory;
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
        $this->tideViewRepository->save(
            $this->tideViewFactory->create($uuid)
        );
    }
}
