<?php

namespace GitHub\Integration;

use JMS\Serializer\Annotation as JMS;

class InstallationAccount
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $login;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $description;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("avatar_url")
     *
     * @var string
     */
    private $avatarUrl;

    /**
     * @param int    $id
     * @param string $login
     * @param string $description
     * @param string $avatarUrl
     */
    public function __construct($id, $login, $description = null, $avatarUrl = null)
    {
        $this->id = $id;
        $this->login = $login;
        $this->description = $description;
        $this->avatarUrl = $avatarUrl;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }
}
