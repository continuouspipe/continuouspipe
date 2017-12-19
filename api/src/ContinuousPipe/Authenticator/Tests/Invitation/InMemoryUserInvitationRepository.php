<?php

namespace ContinuousPipe\Authenticator\Tests\Invitation;

use ContinuousPipe\Authenticator\Invitation\InvitationException;
use ContinuousPipe\Authenticator\Invitation\InvitationNotFound;
use ContinuousPipe\Authenticator\Invitation\UserInvitation;
use ContinuousPipe\Authenticator\Invitation\UserInvitationRepository;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\UuidInterface;

class InMemoryUserInvitationRepository implements UserInvitationRepository
{
    /**
     * @var UserInvitation[]
     */
    private $invitations = [];

    /**
     * {@inheritdoc}
     */
    public function findByUserEmail($email)
    {
        return array_values(array_filter($this->invitations, function (UserInvitation $userInvitation) use ($email) {
            return $userInvitation->getUserEmail() == $email;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function save(UserInvitation $userInvitation)
    {
        return $this->invitations[(string) $userInvitation->getUuid()] = $userInvitation;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(UserInvitation $invitation)
    {
        $uuid = (string) $invitation->getUuid();
        if (!array_key_exists($uuid, $this->invitations)) {
            throw new InvitationException('Not found');
        }

        unset($this->invitations[$uuid]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team)
    {
        return array_values(array_filter($this->invitations, function (UserInvitation $invitation) use ($team) {
            return $invitation->getTeamSlug() == $team->getSlug();
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findByUuid(UuidInterface $uuid)
    {
        if (!array_key_exists((string) $uuid, $this->invitations)) {
            throw new InvitationNotFound(sprintf('Invitation "%s" is not found', (string) $uuid));
        }

        return $this->invitations[(string) $uuid];
    }
}
