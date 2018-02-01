<?php

namespace ContinuousPipe\Authenticator\Security\AccountConnector;

use ContinuousPipe\Security\Account\AccountRepository;
use ContinuousPipe\Security\Account\BitBucketAccount;
use ContinuousPipe\Security\Account\GitHubAccount;
use ContinuousPipe\Security\Account\GoogleAccount;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\Bitbucket2ResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GitHubResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountConnector implements AccountConnectorInterface
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AccountRepository $accountRepository
     * @param LoggerInterface   $logger
     */
    public function __construct(AccountRepository $accountRepository, LoggerInterface $logger)
    {
        $this->accountRepository = $accountRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $resourceOwner = $response->getResourceOwner();

        if ($resourceOwner instanceof GoogleResourceOwner) {
            $account = $this->getAccountFromGoogleResponse($response);
        } elseif ($resourceOwner instanceof GitHubResourceOwner) {
            $account = $this->getAccountFromGitHubResponse($response);
        } elseif ($resourceOwner instanceof Bitbucket2ResourceOwner) {
            $account = $this->getAccountFromBitBucket($response);
        } else {
            return $this->logger->error('Connecting accounts from resource owner of type {type} is not supported', [
                'type' => is_object($resourceOwner) ? get_class($resourceOwner) : (string) $resourceOwner,
                'user' => $user,
                'response' => $response->getData(),
            ]);
        }

        $this->accountRepository->link($user->getUsername(), $account);
    }

    /**
     * @param UserResponseInterface $response
     *
     * @return GoogleAccount
     */
    private function getAccountFromGoogleResponse(UserResponseInterface $response)
    {
        $rawResponse = $response->getData();

        return new GoogleAccount(
            (string) Uuid::uuid4(),
            $rawResponse['id'],
            $rawResponse['email'],
            $response->getRefreshToken(),
            array_key_exists('name', $rawResponse) ? $rawResponse['name'] : null,
            array_key_exists('picture', $rawResponse) ? $rawResponse['picture'] : null
        );
    }

    /**
     * @param UserResponseInterface $response
     *
     * @return GitHubAccount
     */
    private function getAccountFromGitHubResponse(UserResponseInterface $response)
    {
        $rawResponse = $response->getData();

        return new GitHubAccount(
            (string) Uuid::uuid4(),
            $rawResponse['id'],
            $rawResponse['login'],
            $response->getAccessToken(),
            array_key_exists('email', $rawResponse) ? $rawResponse['email'] : null,
            array_key_exists('name', $rawResponse) ? $rawResponse['name'] : null,
            array_key_exists('avatar_url', $rawResponse) ? $rawResponse['avatar_url'] : null
        );
    }

    /**
     * @param UserResponseInterface $response
     *
     * @return BitBucketAccount
     */
    private function getAccountFromBitBucket(UserResponseInterface $response)
    {
        $rawResponse = $response->getData();

        return new BitBucketAccount(
            (string) Uuid::uuid4(),
            $rawResponse['username'],
            $rawResponse['uuid'],
            $response->getEmail(),
            $response->getRefreshToken(),
            $rawResponse['display_name'],
            $response->getProfilePicture()
        );
    }
}
