<?php

namespace spec\ContinuousPipe\Authenticator\Intercom\Normalizer;

use PhpSpec\ObjectBehavior;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Billing\BillingProfile\Trial\TrialResolver;
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
        TrialResolver $trialResolver,
        UserBillingProfileRepository $userBillingProfileRepository
    ) {
        $this->user = new User(self::USERNAME, Uuid::uuid4());
        $this->user->setEmail(self::EMAIL);

        $this->tomorrow = new \DateTimeImmutable('tomorrow');

        $userBillingProfile = new UserBillingProfile(Uuid::uuid4(), $this->user, 'Test', new \DateTimeImmutable('2 weeks ago'), true);

        $userBillingProfileRepository->findByUser($this->user)->willReturn($userBillingProfile);

        $trialResolver->getTrialPeriodExpirationDate($userBillingProfile)->willReturn($this->tomorrow);

        $teamMembershipRepository->findByUser($this->user)->willReturn(
            new ArrayCollection([
                new TeamMembership(new Team(self::TEAM_SLUG, self::TEAM_NAME), $this->user),
            ])
        );

        $this->beConstructedWith($teamMembershipRepository, $trialResolver, $userBillingProfileRepository);
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
            'in_trial' => 'Yes',
            'trial_expiry_date' => $this->tomorrow,
        ] as $key => $value) {
            $normalisedUser->shouldHaveKeyWithValue($key, $value);
        }
    }

    function it_sets_in_trial_value_to_No_for_expired_trial(
        TrialResolver $trialResolver,
        UserBillingProfileRepository $userBillingProfileRepository
    ) {
        $userBillingProfile = new UserBillingProfile(Uuid::uuid4(), $this->user, 'Expired', new \DateTimeImmutable('2 weeks ago'), false);

        $userBillingProfileRepository->findByUser($this->user)->willReturn($userBillingProfile);

        $trialResolver->getTrialPeriodExpirationDate($userBillingProfile)->willReturn(new \DateTimeImmutable('yesterday'));

        $normalisedUser = $this->normalize($this->user);
        $normalisedUser->shouldHaveKeyWithValue('in_trial', 'No');
    }
}
