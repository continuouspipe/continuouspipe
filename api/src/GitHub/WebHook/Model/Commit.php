<?php

namespace GitHub\WebHook\Model;

use JMS\Serializer\Annotation as JMS;

class Commit
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $id;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $distinct;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $message;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $url;

    /**
     * @JMS\Type("GitHub\WebHook\Model\CommitUser")
     * @JMS\Groups({"commit"})
     *
     * @var CommitUser
     */
    private $author;

    /**
     * @JMS\Type("GitHub\WebHook\Model\CommitUser")
     * @JMS\Groups({"commit"})
     *
     * @var CommitUser
     */
    private $committer;

    /**
     * @JMS\Type("array<string>")
     * @JMS\SerializedName("added")
     * @JMS\Groups({"commit"})
     *
     * @var string[]
     */
    private $filesAdded;

    /**
     * @JMS\Type("array<string>")
     * @JMS\SerializedName("removed")
     * @JMS\Groups({"commit"})
     *
     * @var string[]
     */
    private $filesRemoved;

    /**
     * @JMS\Type("array<string>")
     * @JMS\SerializedName("modified")
     * @JMS\Groups({"commit"})
     *
     * @var string[]
     */
    private $filesModified;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isDistinct()
    {
        return $this->distinct;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return CommitUser
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return CommitUser
     */
    public function getCommitter()
    {
        return $this->committer;
    }

    /**
     * @return \string[]
     */
    public function getFilesAdded()
    {
        return $this->filesAdded;
    }

    /**
     * @return \string[]
     */
    public function getFilesRemoved()
    {
        return $this->filesRemoved;
    }

    /**
     * @return \string[]
     */
    public function getFilesModified()
    {
        return $this->filesModified;
    }
}
