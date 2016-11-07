<?php

namespace ContinuousPipe\Google\Http;

use ContinuousPipe\Google\GoogleException;
use ContinuousPipe\Security\Account\GoogleAccount;
use GuzzleHttp\ClientInterface;

interface ClientFactory
{
    /**
     * @param GoogleAccount $account
     *
     * @throws GoogleException
     *
     * @return ClientInterface
     */
    public function fromAccount(GoogleAccount $account);
}
