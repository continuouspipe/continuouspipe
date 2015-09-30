<?php

namespace GitHub\WebHook\Security;

use Symfony\Component\HttpFoundation\Request;

class RequestValidator
{
    const SIGNATURE_HEADER = 'X-Hub-Signature';

    /**
     * @var string
     */
    private $secret;

    /**
     * @param string $secret
     */
    public function __construct($secret = null)
    {
        $this->secret = $secret;
    }

    /**
     * @param Request $request
     *
     * @throws InvalidRequest
     */
    public function validate(Request $request)
    {
        if (null === $this->secret) {
            return;
        }

        $signature = $request->headers->get(self::SIGNATURE_HEADER);
        if (null === $signature) {
            throw new InvalidRequest('No signature header found in request');
        } elseif (strpos($signature, '=') === false) {
            throw new InvalidRequest('Invalid signature found');
        }

        list($method, $gitHubSignature) = explode('=', $signature);

        $payload = $request->getContent();
        $payloadHash = hash_hmac($method, $payload, $this->secret);

        if ($payloadHash != $gitHubSignature) {
            throw new InvalidRequest('Signature is invalid for the given payload');
        }
    }
}
