<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\Security\Account\Account;

class DelegatesToTheExplorerThatSupportsTheAccountExplorer implements CodeRepositoryExplorer
{
    /**
     * @var array|CodeRepositoryExplorer[]
     */
    private $explorers;

    /**
     * @param CodeRepositoryExplorer[] $explorers
     */
    public function __construct(array $explorers)
    {
        $this->explorers = $explorers;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserRepositories(Account $account): array
    {
        foreach ($this->explorers as $explorer) {
            if ($explorer->supports($account)) {
                return $explorer->findUserRepositories($account);
            }
        }

        throw $this->notSupportedException($account);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrganisations(Account $account): array
    {
        foreach ($this->explorers as $explorer) {
            if ($explorer->supports($account)) {
                return $explorer->findOrganisations($account);
            }
        }

        throw $this->notSupportedException($account);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrganisationRepositories(Account $account, string $organisationIdentifier): array
    {
        foreach ($this->explorers as $explorer) {
            if ($explorer->supports($account)) {
                return $explorer->findOrganisationRepositories($account, $organisationIdentifier);
            }
        }

        throw $this->notSupportedException($account);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Account $account): bool
    {
        foreach ($this->explorers as $explorer) {
            if ($explorer->supports($account)) {
                return true;
            }
        }

        return false;
    }

    private function notSupportedException(Account $account): CodeRepositoryException
    {
        return new CodeRepositoryException(sprintf('Account of type "%s" is not supported', get_class($account)));
    }
}
