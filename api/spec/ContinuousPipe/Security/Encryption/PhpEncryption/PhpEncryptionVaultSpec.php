<?php

namespace spec\ContinuousPipe\Security\Encryption\PhpEncryption;

use ContinuousPipe\Security\Encryption\EncryptionException;
use ContinuousPipe\Security\Encryption\PhpEncryption\PhpEncryptionVault;
use ContinuousPipe\Security\Encryption\Vault;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PhpEncryptionVaultSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('def00000fc5829bd0007abaa3a3d6cc641e7b52e3fc0a1e111f6acb243b0ddc7a3a05e85f392af96be6649dbd6469bec95c07a14a751db93cd9bde94547225f751a2e23b');
    }

    function it_is_a_vault()
    {
        $this->shouldImplement(Vault::class);
    }

    function it_can_encrypt_and_decrypt_the_value()
    {
        $value = $this->encrypt('namespace', 'my-value');

        $this->decrypt('namespace', $value)->shouldReturn('my-value');
    }

    function it_will_not_decrypt_the_value_of_different_namespaces()
    {
        $value = $this->encrypt('namespace', 'my-value');

        $this->shouldThrow(EncryptionException::class)->duringDecrypt('wrong-namespace', $value);
    }
}
