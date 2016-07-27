<?php

namespace ContinuousPipe\Authenticator\Team\EventProducer;

use ContinuousPipe\Authenticator\Team\Event\TeamSaved;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TeamEventProducerRepository implements TeamRepository
{
    /**
     * @var TeamRepository
     */
    private $decoratedRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param TeamRepository $decoratedRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(TeamRepository $decoratedRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->decoratedRepository = $decoratedRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Team $team)
    {
        $saved = $this->decoratedRepository->save($team);

        $this->eventDispatcher->dispatch(TeamSaved::EVENT_NAME, new TeamSaved($saved));

        return $saved;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($slug)
    {
        return $this->decoratedRepository->exists($slug);
    }

    /**
     * {@inheritdoc}
     */
    public function find($slug)
    {
        return $this->decoratedRepository->find($slug);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->decoratedRepository->findAll();
    }
}
