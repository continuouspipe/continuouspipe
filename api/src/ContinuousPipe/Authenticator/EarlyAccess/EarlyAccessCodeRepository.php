<?php

namespace ContinuousPipe\Authenticator\EarlyAccess;

interface EarlyAccessCodeRepository
{
    /**
     * @param string $code
     *
     * @return EarlyAccessCode
     *
     * @throws EarlyAccessCodeNotFoundException
     */
    public function findByCode(string $code): EarlyAccessCode;
}
