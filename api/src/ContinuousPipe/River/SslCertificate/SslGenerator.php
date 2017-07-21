<?php

namespace ContinuousPipe\River\SslCertificate;

use ContinuousPipe\Archive\ArchiveException;
use ContinuousPipe\Archive\FileSystemArchive;
use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\Model\Component\Endpoint\SslCertificate;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class SslGenerator
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $hostname
     *
     * @throws SslCertificateException
     *
     * @return SslCertificate
     */
    public function generate(string $hostname) : SslCertificate
    {
        $archive = FileSystemArchive::createEmpty();

        $process = new Process(
            sprintf(
                'openssl req -x509 -nodes -days %d -newkey rsa:2048 -keyout certificate.key -out certificate.crt -subj "/CN=%s/O=ContinuousPipe"',
                10 * 365,
                $hostname
            ),
            $archive->getDirectory()
        );

        try {
            $process->run();
        } catch (\RuntimeException $e) {
            throw new SslCertificateException('Cannot generate the SSL certificate: '.$e->getMessage(), $e->getCode(), $e);
        }

        if (!$process->isSuccessful()) {
            throw new SslCertificateException('Something went wrong while generating certificate: '.$process->getErrorOutput());
        }

        $filesystem = $archive->getFilesystem();

        try {
            $certificate = new SslCertificate(
                'automatic',
                base64_encode($filesystem->getContents('certificate.crt')),
                base64_encode($filesystem->getContents('certificate.key'))
            );
        } catch (FileNotFound $e) {
            throw new SslCertificateException('Cannot find the generated certificates', $e->getCode(), $e);
        }

        try {
            $archive->delete();
        } catch (ArchiveException $e) {
            $this->logger->warning('Did not delete the SSL certificate archive', [
                'exception' => $e,
            ]);
        }

        return $certificate;
    }
}
