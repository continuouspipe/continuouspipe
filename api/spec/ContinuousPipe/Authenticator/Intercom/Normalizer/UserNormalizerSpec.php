<?php

namespace spec\ContinuousPipe\Authenticator\Intercom\Normalizer;

use PhpSpec\ObjectBehavior;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\Team;
use Doctrine\Common\Collections\ArrayCollection;

class UserNormalizerSpec extends ObjectBehavior
{
    const USERNAME = 'jo.bravo';
    const EMAIL = 'jo.bravo@example.org';

    const TEAM_SLUG = 'test-team';
    const TEAM_NAME = 'Test Team';

    /**
     * @var User
     */
    private $user;

    /**
     * @var \DateTimeInterface
     */
    private $tomorrow;

    function let(
        TeamMembershipRepository $teamMembershipRepository,
        UserBillingProfileRepository $userBillingProfileRepository
    ) {
        $this->user = new User(self::USERNAME, Uuid::uuid4());
        $this->user->setEmail(self::EMAIL);

        $this->tomorrow = new \DateTimeImmutable('tomorrow');

        $userBillingProfile = new UserBillingProfile(Uuid::uuid4(), 'Test', new \DateTimeImmutable('2 weeks ago'), [$this->user], new \DateTime('+1 day'));

        $userBillingProfileRepository->findByUser($this->user)->willReturn([$userBillingProfile]);

        $teamMembershipRepository->findByUser($this->user)->willReturn(
            new ArrayCollection([
                new TeamMembership(new Team(self::TEAM_SLUG, self::TEAM_NAME), $this->user),
            ])
        );

        $this->beConstructedWith($teamMembershipRepository, $userBillingProfileRepository);
    }

    function it_normalizes_User_into_array()
    {
        $normalisedUser = $this->normalize($this->user);

        $normalisedUser->shouldBeArray();

        foreach ([
            'user_id' => self::USERNAME,
            'email' => self::EMAIL,
            'name' => self::USERNAME,
            'companies' => [['company_id' => self::TEAM_SLUG, 'name' => self::TEAM_NAME]],
        ] as $key => $value) {
            $normalisedUser->shouldHaveKeyWithValue($key, $value);
        }
    }
}
