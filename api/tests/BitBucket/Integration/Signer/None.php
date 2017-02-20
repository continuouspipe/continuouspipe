<?php

namespace BitBucket\Integration\Signer;

use Lcobucci\JWT\Signature;
use Lcobucci\JWT\Signer;

final class None implements Signer
{
    /**
     * {@inheritdoc}
     */
    public function getAlgorithmId(): string
    {
        return 'none';
    }

    /**
     * {@inheritdoc}
     */
    public function sign($payload, $key): Signature
    {
        return new Signature('');
    }

    /**
     * {@inheritdoc}
     */
    public function verify($expected, $payload, $key): bool
    {
        return $expected === '';
    }

    /**
     * Apply changes on headers according with algorithm
     *
     * @param array $headers
     */
    public function modifyHeader(array &$headers)
    {
        $headers['alg'] = $this->getAlgorithmId();
    }
}