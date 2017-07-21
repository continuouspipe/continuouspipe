<?php

namespace ContinuousPipe\River\SslCertificate;

use ContinuousPipe\Model\Component\Endpoint\SslCertificate;
use Ramsey\Uuid\UuidInterface;

class SslCertificateManager
{
    /**
     * @var SslCertificateRepository
     */
    private $certificateRepository;

    /**
     * @var SslGenerator
     */
    private $sslGenerator;

    /**
     * @param SslCertificateRepository $certificateRepository
     * @param SslGenerator $sslGenerator
     */
    public function __construct(SslCertificateRepository $certificateRepository, SslGenerator $sslGenerator)
    {
        $this->certificateRepository = $certificateRepository;
        $this->sslGenerator = $sslGenerator;
    }

    /**
     * @param UuidInterface $flowUuid
     * @param string $hostname
     *
     * @throws SslCertificateException
     *
     * @return SslCertificate
     */
    public function findOrCreate(UuidInterface $flowUuid, string $hostname)
    {
        if (null === ($certificate = $this->certificateRepository->find($flowUuid, $hostname))) {
            $certificate = $this->sslGenerator->generate($hostname);

            $this->certificateRepository->save($flowUuid, $hostname, $certificate);
        }

        return $certificate;
    }
}
