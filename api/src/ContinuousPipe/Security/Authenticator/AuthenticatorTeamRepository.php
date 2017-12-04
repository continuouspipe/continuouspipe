<?php

namespace ContinuousPipe\Security\Authenticator;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamRepository;

class AuthenticatorTeamRepository implements TeamRepository
{
    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;

    /**
     * @param AuthenticatorClient $authenticatorClient
     */
    public function __construct(AuthenticatorClient $authenticatorClient)
    {
        $this->authenticatorClient = $authenticatorClient;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Team $team)
    {
        if (method_exists($this->authenticatorClient, 'addTeam')) {
            return $this->authenticatorClient->addTeam($team);
        }

        throw new \RuntimeException('Unable to save team to authenticator API');
    }

    /**
     * {@inheritdoc}
     */
    public function exists($slug)
    {
        try {
            $this->find($slug);

            return true;
        } catch (TeamNotFound $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find($slug)
    {
        return $this->authenticatorClient->findTeamBySlug($slug);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->authenticatorClient->findAllTeams();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Team $team)
    {
        if (method_exists($this->authenticatorClient, 'deleteTeam')) {
            return $this->authenticatorClient->deleteTeam($team);
        }

        throw new \RuntimeException('Unable to delete team via authenticator API');
    }
}
