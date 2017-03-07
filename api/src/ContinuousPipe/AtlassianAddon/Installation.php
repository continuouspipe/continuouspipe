<?php

namespace ContinuousPipe\AtlassianAddon;

use JMS\Serializer\Annotation as JMS;

class Installation
{
    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\Account")
     *
     * @var Account
     */
    private $principal;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\Account")
     *
     * @var Account
     */
    private $user;

    /**
     * @JMS\SerializedName("baseUrl")
     * @JMS\Type("string")
     *
     * @var string
     */
    private $baseUrl;

    /**
     * @JMS\SerializedName("baseApiUrl")
     * @JMS\Type("string")
     *
     * @var string
     */
    private $baseApiUrl;

    /**
     * @JMS\SerializedName("publicKey")
     * @JMS\Type("string")
     *
     * @var string
     */
    private $publicKey;

    /**
     * Identifying key for the installation of the add-on in Bitbucket. It is unique across Bitbucket as well as all Atlassian Cloud products.
     *
     * @JMS\SerializedName("clientKey")
     * @JMS\Type("string")
     *
     * @see https://developer.atlassian.com/bitbucket/descriptor/lifecycle.html
     *
     * @var string
     */
    private $clientKey;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $key;

    /**
     * @JMS\SerializedName("sharedSecret")
     * @JMS\Type("string")
     *
     * @var string
     */
    private $sharedSecret;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\OAuthConsumer")
     *
     * @var OAuthConsumer
     */
    private $consumer;

    public function getPrincipal(): Account
    {
        return $this->principal;
    }

    public function getUser(): Account
    {
        return $this->user;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getBaseApiUrl(): string
    {
        return $this->baseApiUrl;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getSharedSecret(): string
    {
        return $this->sharedSecret;
    }

    public function getConsumer(): OAuthConsumer
    {
        return $this->consumer;
    }
}
