<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine\Entity;

use ContinuousPipe\Security\Account\Account;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="account_by_username", columns={"username"})
 * })
 */
class AccountLink
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ContinuousPipe\Security\Account\Account", cascade={"persist"})
     * @ORM\JoinColumn(name="account_uuid", referencedColumnName="uuid")
     *
     * @var Account
     */
    public $account;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     *
     * @var string
     */
    public $username;
}
