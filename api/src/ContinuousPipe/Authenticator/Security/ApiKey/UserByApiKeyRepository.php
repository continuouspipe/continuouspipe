<?php

namespace ContinuousPipe\Authenticator\Security\ApiKey;

use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface UserByApiKeyRepository
{
    /**
     * @param string $key
     *
     * @return UserInterface|null
     */
    public function findUserByApiKey(string $key);

    /**
     * @param UserApiKey $key
     */
    public function save(UserApiKey $key);

    /**
     * @param string $username
     *
     * @return UserApiKey[]
     */
    public function findByUser(string $username);

    /**
     * @param string $username
     * @param UuidInterface $keyUuid
     */
    public function delete(string $username, UuidInterface $keyUuid);
}
