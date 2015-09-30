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
     * @param string $name
     * @param string $url
     * @param bool   $private
     * @param string $id
     */
    public function __construct($name, $url, $private = false, $id = null)
    {
        $this->name = $name;
        $this->url = $url;
        $this->private = $private;
        $this->id = $id;
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
}
