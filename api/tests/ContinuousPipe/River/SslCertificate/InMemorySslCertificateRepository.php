<?php

namespace ContinuousPipe\River\SslCertificate;

use ContinuousPipe\Model\Component\Endpoint\SslCertificate;
use Ramsey\Uuid\UuidInterface;

class InMemorySslCertificateRepository implements SslCertificateRepository
{
    private $store = [];

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $flowUuid, string $hostname)
    {
        $key = $flowUuid->toString().'-'.$hostname;

        return array_key_exists($key, $this->store) ? $this->store[$key] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(UuidInterface $flowUuid, string $hostname, SslCertificate $certificate)
    {
        $this->store[$flowUuid->toString().'-'.$hostname] = $certificate;
    }
}