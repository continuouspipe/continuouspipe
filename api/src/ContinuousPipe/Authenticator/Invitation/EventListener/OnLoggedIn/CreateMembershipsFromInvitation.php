<?php

namespace ContinuousPipe\Authenticator\Invitation\EventListener\OnLoggedIn;

use ContinuousPipe\Authenticator\Invitation\InvitationException;
use ContinuousPipe\Authenticator\Invitation\InvitationToTeamMembershipTransformer;
use ContinuousPipe\Authenticator\Invitation\UserInvitationRepository;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class CreateMembershipsFromInvitation implements EventSubscriberInterface
{
    /**
     * @var UserInvitationRepository
     */
    private $userInvitationRepository;
    /**
     * @var InvitationToTeamMembershipTransformer
     */
    private $invitationToTeamMembershipTransformer;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param UserInvitationRepository              $userInvitationRepository
     * @param InvitationToTeamMembershipTransformer $invitationToTeamMembershipTransformer
     * @param LoggerInterface                       $logger
     */
    public function __construct(UserInvitationRepository $userInvitationRepository, InvitationToTeamMembershipTransformer $invitationToTeamMembershipTransformer, LoggerInterface $logger)
    {
        $this->userInvitationRepository = $userInvitationRepository;
        $this->invitationToTeamMembershipTransformer = $invitationToTeamMembershipTransformer;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'userLoggedIn',
        ];
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function userLoggedIn(InteractiveLoginEvent $event)
    {
        $user = $this->getUserFromEvent($event);
        if (!$user instanceof User) {
            return;
        }

        $invitations = $this->userInvitationRepository->findByUserEmail($user->getEmail());
        foreach ($invitations as $invitation) {
            try {
                $this->invitationToTeamMembershipTransformer->transformInvitation($invitation);
                $this->userInvitationRepository->delete($invitation);
            } catch (InvitationException $e) {
                $this->logger->error($e->getMessage(), [
                    'exception' => $e,
                    'invitation' => $invitation,
                    'user' => $user,
                ]);
            }
        }
    }

    /**
     * @param InteractiveLoginEvent $event
     *
     * @return User|mixed
     */
    private function getUserFromEvent(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof SecurityUser) {
            $user = $user->getUser();
        }

        return $user;
    }
}
