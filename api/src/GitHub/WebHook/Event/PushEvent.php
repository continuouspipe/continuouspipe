<?php

namespace GitHub\WebHook\Event;

use GitHub\WebHook\AbstractEvent;
use GitHub\WebHook\Model\Commit;
use GitHub\WebHook\Model\CommitUser;
use GitHub\WebHook\Model\Repository;
use GitHub\WebHook\Model\User;
use JMS\Serializer\Annotation as JMS;

class PushEvent extends AbstractEvent
{
    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("ref")
     *
     * @var string
     */
    private $reference;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $before;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $after;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $created;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $deleted;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $forced;

    /**
     * @JMS\Type("array<GitHub\WebHook\Model\Commit>")
     * @JMS\Groups({"commit"})
     *
     * @var Commit[]
     */
    private $commits;

    /**
     * @JMS\Type("GitHub\WebHook\Model\Commit")
     * @JMS\SerializedName("head_commit")
     *
     * @var Commit
     */
    private $headCommit;

    /**
     * @var Repository
     *
     * @JMS\Type("GitHub\WebHook\Model\Repository")
     */
    private $repository;

    /**
     * @var CommitUser
     *
     * @JMS\Type("GitHub\WebHook\Model\CommitUser")
     */
    private $pusher;

    /**
     * @var User
     *
     * @JMS\Type("GitHub\WebHook\Model\User")
     */
    private $sender;

    /**
     * PushEvent constructor.
     *
     * @param string                         $reference
     * @param string                         $before
     * @param string                         $after
     * @param bool                           $created
     * @param bool                           $deleted
     * @param bool                           $forced
     * @param \GitHub\WebHook\Model\Commit[] $commits
     * @param Commit                         $headCommit
     * @param Repository                     $repository
     * @param CommitUser                     $pusher
     * @param User                           $sender
     */
    public function __construct(
        $reference = null,
        $before = null,
        $after = null,
        $created = null,
        $deleted = null,
        $forced = null,
        array $commits = [],
        Commit $headCommit = null,
        Repository $repository = null,
        CommitUser $pusher = null,
        User $sender = null
    ) {
        $this->reference = $reference;
        $this->before = $before;
        $this->after = $after;
        $this->created = $created;
        $this->deleted = $deleted;
        $this->forced = $forced;
        $this->commits = $commits;
        $this->headCommit = $headCommit;
        $this->repository = $repository;
        $this->pusher = $pusher;
        $this->sender = $sender;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getBefore()
    {
        return $this->before;
    }

    /**
     * @return string
     */
    public function getAfter()
    {
        return $this->after;
    }

    /**
     * @return bool
     */
    public function isCreated()
    {
        return $this->created;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @return bool
     */
    public function isForced()
    {
        return $this->forced;
    }

    /**
     * @return Commit[]
     */
    public function getCommits()
    {
        return $this->commits ?: [];
    }

    /**
     * @return Commit|null
     */
    public function getHeadCommit()
    {
        return $this->headCommit;
    }

    /**
     * @return Repository|null
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return CommitUser|null
     */
    public function getPusher()
    {
        return $this->pusher;
    }

    /**
     * @return User|null
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'push';
    }
}
