<?php

namespace ContinuousPipe\River\Tests\Repository;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\User\User;
use Rhumsaa\Uuid\Uuid;

class InMemoryFlowRepository implements FlowRepository
{
    private $flowsByUuid = [];
    private $flowsByUser = [];

    /**
     * {@inheritdoc}
     */
    public function save(Flow $flow)
    {
        $this->flowsByUuid[(string) $flow->getUuid()] = $flow;

        $username = $flow->getContext()->getUser()->getUsername();
        if (!array_key_exists($username, $this->flowsByUser)) {
            $this->flowsByUser[$username] = [];
        }
        $this->flowsByUser[$username][] = $flow;

        return $flow;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user)
    {
        $username = $user->getUsername();
        if (!array_key_exists($username, $this->flowsByUser)) {
            return [];
        }

        return $this->flowsByUser[$username];
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Flow $flow)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        if (!array_key_exists((string) $uuid, $this->flowsByUuid)) {
            throw new FlowNotFound();
        }

        return $this->flowsByUuid[(string) $uuid];
    }
}
