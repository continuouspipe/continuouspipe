<?php

namespace GitHub\WebHook\Model;

use JMS\Serializer\Annotation as JMS;

class Repository
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
    private $name;

    /**
     * @var User
     *
     * @JMS\Type("GitHub\WebHook\Model\User")
     */
    private $owner;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $private;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $url;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $defaultBranch;

    /**
     * @param User $owner
     * @param string $name
     * @param string $url
     * @param bool $private
     * @param string $id
     * @param string $defaultBranch
     */
    public function __construct(User $owner, $name, $url, $private = false, $id = null, string $defaultBranch = null)
    {
        $this->owner = $owner;
        $this->name = $name;
        $this->url = $url;
        $this->private = $private;
        $this->id = $id;
        $this->defaultBranch = $defaultBranch;
    }

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->private;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getDefaultBranch()
    {
        return $this->defaultBranch;
    }
}
