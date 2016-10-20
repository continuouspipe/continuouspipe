<?php

namespace GitHub\WebHook\Event;

use GitHub\WebHook\AbstractEvent;
use GitHub\WebHook\Event;
use GitHub\WebHook\Model\Branch;
use GitHub\WebHook\Model\Commit;
use GitHub\WebHook\Model\Repository;
use GitHub\WebHook\Model\User;
use JMS\Serializer\Annotation as JMS;

class StatusEvent extends AbstractEvent
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $context;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $state;

    /**
     * @JMS\Type("GitHub\WebHook\Model\Commit")
     *
     * @var Commit
     */
    private $commit;

    /**
     * @JMS\Type("array<GitHub\WebHook\Model\Branch>")
     *
     * @var Branch[]
     */
    private $branches;

    /**
     * @JMS\Type("GitHub\WebHook\Model\Repository")
     *
     * @var Repository
     */
    private $repository;

    /**
     * @JMS\Type("GitHub\WebHook\Model\User")
     *
     * @var User
     */
    private $sender;

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return Commit
     */
    public function getCommit()
    {
        return $this->commit;
    }

    /**
     * @return \GitHub\WebHook\Model\Branch[]
     */
    public function getBranches()
    {
        return $this->branches;
    }

    /**
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return mixed
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
        return 'status';
    }
}
