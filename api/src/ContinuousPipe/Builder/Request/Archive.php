<?php

namespace ContinuousPipe\Builder\Request;

class Archive
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param string $url
     * @param array $headers
     */
    public function __construct(string $url, array $headers = [])
    {
        $this->url = $url;
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers ?: [];
    }
}
