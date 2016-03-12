<?php

namespace GitHub\WebHook\Model;

use JMS\Serializer\Annotation as JMS;

class Organisation
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $login;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $reposUrl;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $avatarUrl;

    /**
     * @param string $name
     * @param string $reposUrl
     */
    public function __construct($login, $reposUrl)
    {
        $this->login = $login;
        $this->reposUrl = $reposUrl;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getReposUrl()
    {
        return $this->reposUrl;
    }

    /**
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }
}
