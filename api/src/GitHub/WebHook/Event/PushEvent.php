<?php

namespace GitHub\WebHook\Event;

use GitHub\WebHook\Event;
use GitHub\WebHook\Model\Commit;
use GitHub\WebHook\Model\CommitUser;
use GitHub\WebHook\Model\Repository;
use GitHub\WebHook\Model\User;
use JMS\Serializer\Annotation as JMS;

class PushEvent implements Event
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
     * @return \GitHub\WebHook\Model\Commit[]
     */
    public function getCommits()
    {
        return $this->commits;
    }

    /**
     * @return Commit
     */
    public function getHeadCommit()
    {
        return $this->headCommit;
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return CommitUser
     */
    public function getPusher()
    {
        return $this->pusher;
    }

    /**
     * @return User
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
