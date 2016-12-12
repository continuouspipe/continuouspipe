<?php

namespace GitHub\WebHook\Guzzle;

use GuzzleHttp\Client;

class DefaultGuzzleClient extends Client
{
    protected $options = array(
        'base_uri' => 'https://api.github.com/',
        'timeout' => 10,
        'api_limit' => 5000,
        'cache_dir' => null,
    );

    public function __construct(array $config = [])
    {
        $config = array_merge($this->options, $config);

        parent::__construct($config);
    }
}
