<?php

namespace ContinuousPipe\River\Task\ManualApproval;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventBased\ApplyAndRaiseEventCapability;
use ContinuousPipe\River\Task\ManualApproval\Event\Approved;
use ContinuousPipe\River\Task\ManualApproval\Event\ManualApprovalChoiceEvent;
use ContinuousPipe\River\Task\ManualApproval\Event\Rejected;
use ContinuousPipe\River\Task\ManualApproval\Event\TaskCreated;
use ContinuousPipe\River\Task\ManualApproval\Event\WaitingApproval;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskEvent;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\Security\User\User;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Complex;
use LogStream\Node\Node;
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
    private $approvalLogIdentifier;

    /**
     * @var \DateTimeInterface|null
     */
    private $choiceDateTime;

    /**
     * @var User|null
     */
    private $choiceUser;

    public function start(Tide $tide, LoggerFactory $loggerFactory)
    {
        if ($this->status == Task::STATUS_RUNNING) {
            throw new \RuntimeException('The task is already running');
        }

        $label = 'Waiting manual approval';
        $logger = $loggerFactory->from($tide->getLog())->child(new Text($label))->updateStatus(Log::RUNNING);

        $choiceLog = $logger->child($this->createManualApprovalLogNode());

        $this->raiseAndApply(new WaitingApproval(
            $this->tideUuid,
            $this->identifier,
            $logger->getLog()->getId(),
            $label,
            $choiceLog->getLog()->getId()
        ));
    }

    public function approve(LoggerFactory $loggerFactory, User $user)
    {
        $this->choice($loggerFactory, $user, Approved::class);
    }

    public function reject(LoggerFactory $loggerFactory, User $user)
    {
        $this->choice($loggerFactory, $user, Rejected::class);
    }

    private function choice(LoggerFactory $loggerFactory, User $user, string $eventClass)
    {
        if ($this->status != Task::STATUS_RUNNING) {
            throw new \RuntimeException('It is not awaiting for approval anymore.');
        }

        $event = new $eventClass(
            $this->tideUuid,
            $this->identifier,
            $user
        );

        $this->apply($event);

        $loggerFactory->fromId($this->logIdentifier)->updateStatus($this->status == self::STATUS_SUCCESSFUL ? Log::SUCCESS : Log::FAILURE);
        $loggerFactory->fromId($this->approvalLogIdentifier)->update($this->createManualApprovalLogNode());

        $this->raise($event);
    }

    public function applyWaitingApproval(WaitingApproval $event)
    {
        $this->status = Task::STATUS_RUNNING;
        $this->logIdentifier = $event->getLogIdentifier();
        $this->label = $event->getLabel();
        $this->approvalLogIdentifier = $event->getApprovalLogIdentifier();
    }

    public function applyApproved(Approved $event)
    {
        $this->applyChoice($event);
    }

    public function applyRejected(Rejected $event)
    {
        $this->applyChoice($event);
    }

    private function applyChoice(ManualApprovalChoiceEvent $event)
    {
        if ($event instanceof Approved) {
            $this->status = Task::STATUS_SUCCESSFUL;
        } elseif ($event instanceof Rejected) {
            $this->status = Task::STATUS_FAILED;
        }

        $this->choiceDateTime = $event->getDateTime();
        $this->choiceUser = $event->getUser();
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

    private function createManualApprovalLogNode(): Node
    {
        $data = [
            'tide_uuid' => (string) $this->tideUuid,
            'task_identifier' => $this->identifier,
            'status' => $this->getLogStatus(),
        ];

        if ($this->choiceDateTime) {
            $data['choice_datetime'] = $this->choiceDateTime->format(\DateTime::ISO8601);
        }
        if ($this->choiceUser) {
            $data['choice_user'] = $this->choiceUser->getUsername();
        }

        return new Complex('manual_approval', $data);
    }

    private function getLogStatus()
    {
        switch ($this->status) {
            case Task::STATUS_SUCCESSFUL:
                return Log::SUCCESS;
            case Task::STATUS_FAILED:
                return Log::FAILURE;
            case Task::STATUS_RUNNING:
                return Log::RUNNING;
        }

        return Log::PENDING;
    }
}
