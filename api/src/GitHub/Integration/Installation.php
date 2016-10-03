<?php

namespace GitHub\Integration;

use JMS\Serializer\Annotation as JMS;

class Installation
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("GitHub\Integration\InstallationAccount")
     *
     * @var InstallationAccount
     */
    private $account;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return InstallationAccount
     */
    public function getAccount()
    {
        return $this->account;
    }
}
