<?php

namespace ContinuousPipe\River\Flow\EncryptedVariable\GoogleKms;

use ContinuousPipe\River\Flow\EncryptedVariable\EncryptedVariableVault;
use ContinuousPipe\River\Flow\EncryptedVariable\EncryptionException;
use Ramsey\Uuid\UuidInterface;

class GoogleKmsEncryptedVariableVault implements EncryptedVariableVault
{
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
    private $serviceAccountPath;

    /**
     * @var string
     */
    private $keyRing;

    public function __construct(string $projectId, string $location, string $keyRing, string $serviceAccountPath)
    {
        $this->projectId = $projectId;
        $this->location = $location;
        $this->keyRing = $keyRing;
        $this->serviceAccountPath = $serviceAccountPath;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(UuidInterface $flowUuid, string $plainValue): string
    {
        $kms = $this->createKmsClient();
        $key = $this->getFlowKey($kms, $flowUuid);

        try {
            $encryptedResponse = $kms->projects_locations_keyRings_cryptoKeys->encrypt(
                $key->getName(),
                new \Google_Service_CloudKMS_EncryptRequest([
                    'plaintext' => base64_encode($plainValue),
                ])
            );
        } catch (\Google_Exception $e) {
            throw new EncryptionException('Unable to encrypt the value using flow\'s encryption key', $e->getCode(), $e);
        }

        return $encryptedResponse->ciphertext;
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(UuidInterface $flowUuid, string $encryptedValue): string
    {
        $kms = $this->createKmsClient();
        $key = $this->getFlowKey($kms, $flowUuid);

        try {
            $decryptedResponse = $kms->projects_locations_keyRings_cryptoKeys->decrypt(
                $key->getName(),
                new \Google_Service_CloudKMS_DecryptRequest([
                    'ciphertext' => $encryptedValue,
                ])
            );
        } catch (\Google_Exception $e) {
            throw new EncryptionException('Unable to decrypt the value using flow\'s encryption key', $e->getCode(), $e);
        }

        return base64_decode($decryptedResponse->plaintext);
    }

    private function createKmsClient(): \Google_Service_CloudKMS
    {
        // Instantiate the client
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . realpath($this->serviceAccountPath));

        $client = new \Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes(array(
            'https://www.googleapis.com/auth/cloud-platform'
        ));

        // Instantiate the Key Management Service API
        return new \Google_Service_CloudKMS($client);
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

    /**
     * @param \Google_Service_CloudKMS $kms
     * @param UuidInterface $flowUuid
     *
     * @throws EncryptionException
     *
     * @return \Google_Service_CloudKMS_CryptoKey
     */
    private function getFlowKey(\Google_Service_CloudKMS $kms, UuidInterface $flowUuid)
    {
        $keyRingPath = $this->getKeyRingPath();
        $keyName = 'flow-' . $flowUuid->toString();

        try {
            $key = $kms->projects_locations_keyRings_cryptoKeys->get($keyRingPath.'/cryptoKeys/'.$keyName);
        } catch (\Google_Exception $e) {
            if ($e->getCode() == 404) {
                try {
                    $key = $kms->projects_locations_keyRings_cryptoKeys->create($keyRingPath, new \Google_Service_CloudKMS_CryptoKey([
                        'purpose' => 'ENCRYPT_DECRYPT',
                    ]), [
                        'cryptoKeyId' => $keyName,
                    ]);
                } catch (\Google_Exception $e) {
                    throw new EncryptionException('Unable to create the cryptographic key for the flow.', $e->getCode(), $e);
                }
            } else {
                throw new EncryptionException('Unable to get the cryptographic key for the flow.', $e->getCode(), $e);
            }
        }

        return $key;
    }
}
