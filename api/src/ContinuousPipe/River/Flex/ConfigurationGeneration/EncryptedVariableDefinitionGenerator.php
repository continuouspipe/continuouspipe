<?php

namespace ContinuousPipe\River\Flex\ConfigurationGeneration;

use ContinuousPipe\Flex\Variables\VariableDefinitionGenerator;
use ContinuousPipe\River\Flow\EncryptedVariable\EncryptedVariableVault;
use Ramsey\Uuid\UuidInterface;

class EncryptedVariableDefinitionGenerator implements VariableDefinitionGenerator
{
    /**
     * @var EncryptedVariableVault
     */
    private $encryptedVariableVault;

    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @param EncryptedVariableVault $encryptedVariableVault
     * @param UuidInterface $flowUuid
     */
    public function __construct(EncryptedVariableVault $encryptedVariableVault, UuidInterface $flowUuid)
    {
        $this->encryptedVariableVault = $encryptedVariableVault;
        $this->flowUuid = $flowUuid;
    }

    public function generateDefinition(string $name, string $value): array
    {
        return [
            'name' => $name,
            'encrypted_value' => $this->encryptedVariableVault->encrypt($this->flowUuid, $value),
        ];
    }
}
