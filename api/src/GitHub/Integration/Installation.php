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
     * @param int                 $id
     * @param InstallationAccount $account
     */
    public function __construct($id, InstallationAccount $account)
    {
        $this->id = $id;
        $this->account = $account;
    }

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
