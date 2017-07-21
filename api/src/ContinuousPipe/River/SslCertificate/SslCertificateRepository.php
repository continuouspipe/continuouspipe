<?php

namespace ContinuousPipe\River\SslCertificate;

use ContinuousPipe\Model\Component\Endpoint\SslCertificate;
use Ramsey\Uuid\UuidInterface;

interface SslCertificateRepository
{
    /**
     * @param UuidInterface $flowUuid
     * @param string $hostname
     *
     * @return SslCertificate|null
     */
    public function find(UuidInterface $flowUuid, string $hostname);

    /**
     * @param UuidInterface $flowUuid
     * @param string $hostname
     * @param SslCertificate $certificate
     */
    public function save(UuidInterface $flowUuid, string $hostname, SslCertificate $certificate);
}
