<?php

namespace ContinuousPipe\Model\Component;

use ContinuousPipe\Model\Component\Endpoint\CloudFlareZone;
use ContinuousPipe\Model\Component\Endpoint\EndpointIngress;
use ContinuousPipe\Model\Component\Endpoint\HttpLabs;
use ContinuousPipe\Model\Component\Endpoint\SslCertificate;

class Endpoint
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $annotations;

    /**
     * @var SslCertificate[]
     */
    private $sslCertificates;

    /**
     * @var CloudFlareZone|null
     */
    private $cloudFlareZone;

    /**
     * @var HttpLabs|null
     */
    private $httpLabs;

    /**
     * @var EndpointIngress|null
     */
    private $ingress;

    /**
     * @param string $name
     * @param string $type
     * @param array $annotations
     * @param SslCertificate[] $sslCertificates
     * @param CloudFlareZone|null $cloudFlareZone
     * @param HttpLabs|null $httpLabs
     * @param EndpointIngress|null $ingress
     */
    public function __construct(
        string $name,
        string $type = null,
        array $annotations = [],
        array $sslCertificates = [],
        CloudFlareZone $cloudFlareZone = null,
        HttpLabs $httpLabs = null,
        EndpointIngress $ingress = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->annotations = $annotations;
        $this->sslCertificates = $sslCertificates;
        $this->cloudFlareZone = $cloudFlareZone;
        $this->httpLabs = $httpLabs;
        $this->ingress = $ingress;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return SslCertificate[]
     */
    public function getSslCertificates()
    {
        return $this->sslCertificates ?: [];
    }

    /**
     * @return array
     */
    public function getAnnotations(): array
    {
        return $this->annotations ?: [];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return CloudFlareZone|null
     */
    public function getCloudFlareZone()
    {
        return $this->cloudFlareZone;
    }

    /**
     * @return HttpLabs|null
     */
    public function getHttpLabs()
    {
        return $this->httpLabs;
    }

    /**
     * @return EndpointIngress|null
     */
    public function getIngress()
    {
        return $this->ingress;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @param array $annotations
     */
    public function setAnnotations(array $annotations)
    {
        $this->annotations = $annotations;
    }

    /**
     * @param SslCertificate[] $sslCertificates
     */
    public function setSslCertificates(array $sslCertificates)
    {
        $this->sslCertificates = $sslCertificates;
    }

    /**
     * @param CloudFlareZone|null $cloudFlareZone
     */
    public function setCloudFlareZone($cloudFlareZone)
    {
        $this->cloudFlareZone = $cloudFlareZone;
    }

    /**
     * @param EndpointIngress|null $ingress
     */
    public function setIngress($ingress)
    {
        $this->ingress = $ingress;
    }
}
