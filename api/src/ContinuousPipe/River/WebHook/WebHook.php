<?php

namespace ContinuousPipe\River\WebHook;

use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\River\CodeReference;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class WebHook
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $uuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $url;

    /**
     * @JMS\Type("ContinuousPipe\River\CodeReference")
     *
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @JMS\Type("array<ContinuousPipe\Pipe\Client\PublicEndpoint>")
     *
     * @var PublicEndpoint[]
     */
    private $publicEndpoints;

    /**
     * @param Uuid             $uuid
     * @param string           $url
     * @param CodeReference    $codeReference
     * @param PublicEndpoint[] $publicEndpoints
     */
    public function __construct(Uuid $uuid, $url, CodeReference $codeReference, array $publicEndpoints)
    {
        $this->uuid = $uuid;
        $this->url = $url;
        $this->codeReference = $codeReference;
        $this->publicEndpoints = $publicEndpoints;
    }

    /**
     * @return PublicEndpoint[]
     */
    public function getPublicEndpoints(): array
    {
        return $this->publicEndpoints;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return CodeReference
     */
    public function getCodeReference(): CodeReference
    {
        return $this->codeReference;
    }
}
