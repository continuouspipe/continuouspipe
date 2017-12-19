<?php

namespace ContinuousPipe\Authenticator\Security\ApiKey;

use ContinuousPipe\Security\ApiKey\UserApiKey;
use ContinuousPipe\Security\ApiKey\UserApiKeyRepository;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;

class UserApiKeyFactory
{
    /**
     * @var UserApiKeyRepository
     */
    private $userApiKeyRepository;

    /**
     * @var ApiKeyUuidGenerator
     */
    private $uuidGenerator;

    public function __construct(UserApiKeyRepository $userApiKeyRepository, ApiKeyUuidGenerator $uuidGenerator)
    {
        $this->userApiKeyRepository = $userApiKeyRepository;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function create(User $user, string $name) : UserApiKey
    {
        $uuid = $this->uuidGenerator->generate();
        $apiKey = new UserApiKey(
            $uuid,
            $user,
            $uuid->toString(),
            new \DateTime(),
            $name
        );

        $this->userApiKeyRepository->save($apiKey);

        return $apiKey;
    }
}
