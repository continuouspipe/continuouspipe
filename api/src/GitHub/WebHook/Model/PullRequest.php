<?php

namespace GitHub\WebHook\Model;

use JMS\Serializer\Annotation as JMS;

class PullRequest
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $number;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $title;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $state;

    /**
     * @var CommitReference
     *
     * @JMS\Type("GitHub\WebHook\Model\CommitReference")
     */
    private $head;

    /**
     * @var CommitReference
     *
     * @JMS\Type("GitHub\WebHook\Model\CommitReference")
     */
    private $base;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return CommitReference
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * @return CommitReference
     */
    public function getBase()
    {
        return $this->base;
    }
}
