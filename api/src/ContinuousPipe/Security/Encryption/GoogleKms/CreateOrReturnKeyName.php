<?php

namespace ContinuousPipe\Security\Encryption\GoogleKms;

use ContinuousPipe\Security\Encryption\EncryptionException;

class CreateOrReturnKeyName implements GoogleKmsKeyResolver
{
    /**
     * @var GoogleKmsClientResolver
     */
    private $clientResolver;
    /**
     * @var string
     */
    private $projectId;
    /**
     * @var string
     */
    private $location;
    /**
     * @var string
     */
    private $keyRing;

    public function __construct(GoogleKmsClientResolver $clientResolver, string $projectId, string $location, string $keyRing)
    {
        $this->clientResolver = $clientResolver;
        $this->projectId = $projectId;
        $this->location = $location;
        $this->keyRing = $keyRing;
    }

    public function keyName(string $namespace): string
    {
        $kms = $key = $this->clientResolver->get();
        $keyRingPath = $this->getKeyRingPath();

        try {
            $key = $kms->projects_locations_keyRings_cryptoKeys->get($keyRingPath.'/cryptoKeys/'.$namespace);
        } catch (\Google_Exception $e) {
            if ($e->getCode() == 404) {
                try {
                    $key = $kms->projects_locations_keyRings_cryptoKeys->create($keyRingPath, new \Google_Service_CloudKMS_CryptoKey([
                        'purpose' => 'ENCRYPT_DECRYPT',
                    ]), [
                        'cryptoKeyId' => $namespace,
                    ]);
                } catch (\Google_Exception $e) {
                    throw new EncryptionException('Unable to create the cryptographic key for the flow.', $e->getCode(), $e);
                }
            } else {
                throw new EncryptionException('Unable to get the cryptographic key for the flow.', $e->getCode(), $e);
            }
        }

        return $key->getName();
    }

    private function getKeyRingPath(): string
    {
        return sprintf(
            'projects/%s/locations/%s/keyRings/%s',
            $this->projectId,
            $this->location,
            $this->keyRing
        );
    }
}
