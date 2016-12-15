<?php

namespace ContinuousPipe\River\Task\ManualApproval;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventBased\ApplyAndRaiseEventCapability;
use ContinuousPipe\River\Task\ManualApproval\Event\Approved;
use ContinuousPipe\River\Task\ManualApproval\Event\Rejected;
use ContinuousPipe\River\Task\ManualApproval\Event\TaskCreated;
use ContinuousPipe\River\Task\ManualApproval\Event\WaitingApproval;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskEvent;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\Security\User\User;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

final class ManualApprovalTask implements Task
{
    use ApplyAndRaiseEventCapability {
        apply as doApply;
    }

    private $tideUuid;
    private $identifier;
    private $logIdentifier;
    private $label = 'Manual approval required';
    private $status = Task::STATUS_PENDING;

    public function start(Tide $tide, LoggerFactory $loggerFactory)
    {
        if ($this->status == Task::STATUS_RUNNING) {
            throw new \RuntimeException('The task is already running');
        }

        $label = 'Waiting manual approval';
        $logger = $loggerFactory->from($tide->getLog())->child(new Text($label));

        $this->raiseAndApply(new WaitingApproval(
            $this->tideUuid,
            $this->identifier,
            $logger->getLog()->getId(),
            $label
        ));
    }

    public function approve(User $user)
    {
        $this->choice(Approved::class, $user);
    }

    public function reject(User $user)
    {
        $this->choice(Rejected::class, $user);
    }

    private function choice(string $eventClass, User $user)
    {
        if ($this->status != Task::STATUS_RUNNING) {
            throw new \RuntimeException('It is not awaiting for approval anymore.');
        }

        $this->raiseAndApply(new $eventClass(
            $this->tideUuid,
            $this->identifier,
            $user
        ));
    }

    public function applyWaitingApproval(WaitingApproval $event)
    {
        $this->status = Task::STATUS_RUNNING;
        $this->logIdentifier = $event->getLogIdentifier();
        $this->label = $event->getLabel();
    }

    public function applyApproved()
    {
        $this->status = Task::STATUS_SUCCESSFUL;
    }

    public function applyRejected()
    {
        $this->status = Task::STATUS_FAILED;
    }

    public function applyTaskCreated(TaskCreated $event)
    {
        $this->identifier = $event->getTaskId();
        $this->tideUuid = $event->getTideUuid();
    }

    private function raiseAndApply(TideEvent $event)
    {
        $this->raise($event);
        $this->apply($event);
    }

    /**
     * {@inheritdoc}
     */
    public function accept(TideEvent $event)
    {
        return $event instanceof TaskEvent && $event->getTaskId() == $this->getIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function apply(TideEvent $event)
    {
        $this->doApply($event);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function popNewEvents()
    {
        $events = $this->raisedEvents();

        $this->eraseEvents();

        return $events;
    }

    /**
     * {@inheritdoc}
     */
    public function getExposedContext()
    {
        return new ArrayObject([]);
    }
}
