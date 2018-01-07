<?php

namespace ContinuousPipe\Google\Token;

use ContinuousPipe\Google\GoogleException;
use ContinuousPipe\Security\Account\GoogleAccount;

interface TokenResolver
{
    /**
     * Get an access token for the given account.
     *
     * @param GoogleAccount $account
     *
     * @throws GoogleException
     *
     * @return Token
     */
    public function forAccount(GoogleAccount $account);
}
