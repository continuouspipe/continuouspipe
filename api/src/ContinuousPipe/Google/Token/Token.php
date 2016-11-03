<?php

namespace ContinuousPipe\Google\Token;

use JMS\Serializer\Annotation as JMS;

final class Token
{
    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("access_token")
     *
     * @var string
     */
    private $accessToken;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("token_type")
     *
     * @var string
     */
    private $tokenType;

    /**
     * @JMS\Type("integer")
     * @JMS\SerializedName("expires_in")
     *
     * @var int
     */
    private $expiresIn;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("id_token")
     *
     * @var string
     */
    private $idToken;

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getTokenType() : string
    {
        return $this->tokenType;
    }

    /**
     * @return int
     */
    public function getExpiresIn() : int
    {
        return $this->expiresIn;
    }

    /**
     * @return string
     */
    public function getIdToken() : string
    {
        return $this->idToken;
    }
}
