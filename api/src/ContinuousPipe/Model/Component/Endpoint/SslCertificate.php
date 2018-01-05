<?php

namespace ContinuousPipe\Model\Component\Endpoint;

class SslCertificate
{
    /**
     * @var string
     */
    private $name;

    /**
     * base64 encoded certificate.
     *
     * @var string
     */
    private $cert;

    /**
     * base64 encoded key.
     *
     * @var string
     */
    private $key;

    /**
     * @param string $name
     * @param string $cert
     * @param string $key
     */
    public function __construct(string $name = null, string $cert = null, string $key = null)
    {
        $this->name = $name;
        $this->cert = $cert;
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCert()
    {
        return $this->cert;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}
