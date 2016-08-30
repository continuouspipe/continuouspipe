<?php

namespace GitHub\WebHook\Guzzle\Listener;

use Github\Client;
use Github\Exception\RuntimeException;
use GuzzleHttp\Event\BeforeEvent;

class AuthListener
{
    private $tokenOrLogin;
    private $password;
    private $method;

    public function __construct($tokenOrLogin, $password, $method)
    {
        $this->tokenOrLogin = $tokenOrLogin;
        $this->password = $password;
        $this->method = $method;
    }

    public function beforeRequest(BeforeEvent $event)
    {
        // Skip by default
        if (null === $this->method) {
            return;
        }

        $request = $event->getRequest();
        switch ($this->method) {
            case Client::AUTH_HTTP_PASSWORD:
                $request->setHeader(
                    'Authorization',
                    sprintf('Basic %s', base64_encode($this->tokenOrLogin.':'.$this->password))
                );
                break;

            case Client::AUTH_HTTP_TOKEN:
                $request->setHeader('Authorization', sprintf('token %s', $this->tokenOrLogin));
                break;

            case Client::AUTH_URL_CLIENT_ID:
                $url = $request->getUrl();

                $parameters = array(
                    'client_id' => $this->tokenOrLogin,
                    'client_secret' => $this->password,
                );

                $url .= (false === strpos($url, '?') ? '?' : '&');
                $url .= utf8_encode(http_build_query($parameters, '', '&'));

                $request->setUrl($url);
                break;

            case Client::AUTH_URL_TOKEN:
                $url = $request->getUrl();
                $url .= (false === strpos($url, '?') ? '?' : '&');
                $url .= utf8_encode(http_build_query(array('access_token' => $this->tokenOrLogin), '', '&'));

                $request->setUrl($url);
                break;

            default:
                throw new RuntimeException(sprintf('%s not yet implemented', $this->method));
                break;
        }
    }
}
