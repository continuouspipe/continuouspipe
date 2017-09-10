<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Entity;

use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_certificate_by_flow_hostname", columns={"flow_uuid", "hostname"}),
 * })
 */
class SslCertificateStorage
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     *
     * @var UuidInterface
     */
    public $flowUuid;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     *
     * @var string
     */
    public $hostname;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    public $cert;

    /**
     * @ORM\Column(type="text", name="cert_key")
     *
     * @var string
     */
    public $key;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    public $name;
}
