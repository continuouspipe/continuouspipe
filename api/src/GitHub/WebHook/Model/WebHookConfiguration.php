<?php

namespace GitHub\WebHook\Model;

class WebHookConfiguration
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var null|string
     */
    private $secret;

    /**
     * @var bool
     */
    private $insecureSsl;

    /**
     * @param string $url
     * @param string $contentType
     * @param string $secret
     * @param bool   $insecureSsl
     */
    public function __construct($url, $contentType, $secret = null, $insecureSsl = false)
    {
        $this->url = $url;
        $this->contentType = $contentType;
        $this->secret = $secret;
        $this->insecureSsl = $insecureSsl;
    }
}
