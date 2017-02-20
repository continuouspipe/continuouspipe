<?php

namespace ContinuousPipe\AtlassianAddonBundle\Request\WebHook\Security;

use ContinuousPipe\AtlassianAddon\Installation;
use ContinuousPipe\AtlassianAddon\InstallationRepository;
use ContinuousPipe\AtlassianAddonBundle\Request\WebHook\Security\Jwt\SignerFactory;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Symfony\Component\HttpFoundation\Request;

class RequestValidator
{
    const AUTHORIZATION_HEADER = 'Authorization';
    const TOKEN_TYPE_JSON_WEB_TOKEN = 'JWT';
    const DEFAULT_SIGNING_ALGORITHM = 'HS256';

    /**
     * @var SignerFactory
     */
    private $signerFactory;

    /**
     * @var InstallationRepository
     */
    private $installationRepository;

    public function __construct(SignerFactory $signerFactory, InstallationRepository $installationRepository)
    {
        $this->signerFactory = $signerFactory;
        $this->installationRepository = $installationRepository;
    }

    /**
     * @param Request $request
     *
     * @throws InvalidRequest
     */
    public function validate(Request $request)
    {
        $authorizationHeader = $request->headers->get(self::AUTHORIZATION_HEADER);
        if (empty($authorizationHeader)) {
            throw new InvalidRequest('No authorization header found in request.');
        }

        list($tokenType, $tokenValue) = explode(' ', $authorizationHeader);

        if ($tokenType !== self::TOKEN_TYPE_JSON_WEB_TOKEN) {
            throw new InvalidRequest(sprintf('Unsupported token type given "%s".', $tokenType));
        }

        $token = $this->createToken($tokenValue);
        if (!$this->isValid($token)) {
            throw new InvalidRequest('The provided JSON Web Token is not valid.');
        }

        try {
            /** @var Installation $installation */
            if (!$this->hasGoodSignature($request, $token)) {
                throw new InvalidRequest('The signature of JSON Web Token is not valid.');
            }
        } catch (\InvalidArgumentException $e) {
            throw new InvalidRequest($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function createToken(string $tokenValue): Token
    {
        $parser = new Parser();
        return $parser->parse($tokenValue);
    }

    private function isValid(Token $token): bool
    {
        $validationData = new ValidationData();
        return $token->validate($validationData);
    }

    private function isSignatureValid(Token $token, string $secretKey): bool
    {
        $algorithmId = $token->getHeader('alg', self::DEFAULT_SIGNING_ALGORITHM);
        return $token->verify($this->signerFactory->create($algorithmId), $secretKey);
    }

    private function hasGoodSignature(Request $request, Token $token): bool
    {
        $hasGoodSignature = false;
        foreach ($this->findInstallationsByRequest($request) as $installation) {
            $hasGoodSignature = $hasGoodSignature || $this->isSignatureValid($token, $installation->getSharedSecret());
        }
        return $hasGoodSignature;
    }

    /**
     * @param Request $request
     *
     * @return array <Installation>
     * @throws InvalidRequest
     */
    private function findInstallationsByRequest(Request $request): array
    {
        $decoded = \GuzzleHttp\json_decode($request->getContent(), true);
        if (!isset($decoded['data']['repository']['owner']['username'])) {
            throw new InvalidRequest('No repository username is specified in request body.');
        }
        if (!isset($decoded['data']['repository']['owner']['type'])) {
            throw new InvalidRequest('No repository owner type is specified in request body.');
        }

        return $this->installationRepository->findByPrincipal(
            $decoded['data']['repository']['owner']['type'],
            $decoded['data']['repository']['owner']['username']
        );
    }
}
