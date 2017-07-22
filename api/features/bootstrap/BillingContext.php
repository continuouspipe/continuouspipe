<?php

use AppBundle\Command\MigrateUserActivitiesCommand;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Billing\ActivityTracker\TracedActivityTracker;
use ContinuousPipe\Billing\Usage\Usage;
use ContinuousPipe\Billing\Usage\UsageTracker;
use ContinuousPipe\Message\UserActivity;
use ContinuousPipe\Message\UserActivityUser;
use ContinuousPipe\Security\Team\Team;
use JMS\Serializer\SerializerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Predis\ClientInterface;
use Ramsey\Uuid\Uuid;

class BillingContext implements Context
{
    private $messageConsumer;
    private $tracedActivityTracker;

    /**
     * @var UserActivity[]
     */
    private $activities;
    /**
     * @var UsageTracker
     */
    private $usageTracker;

    /**
     * @var Usage|null
     */
    private $usage;

    /**
     * @var MigrateUserActivitiesCommand
     */
    private $migrateUserActivitiesCommand;

    /**
     * @var ClientInterface
     */
    private $redisClient;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var int
     */
    private $activitiesCount;

    public function __construct(
        ConsumerInterface $messageConsumer,
        TracedActivityTracker $tracedActivityTracker,
        UsageTracker $usageTracker,
        MigrateUserActivitiesCommand $migrateUserActivitiesCommand,
        ClientInterface $redisClient,
        SerializerInterface $serializer
    ) {
        $this->messageConsumer = $messageConsumer;
        $this->tracedActivityTracker = $tracedActivityTracker;
        $this->usageTracker = $usageTracker;
        $this->migrateUserActivitiesCommand = $migrateUserActivitiesCommand;
        $this->redisClient = $redisClient;
        $this->serializer = $serializer;
    }

    /**
     * @When I calculate the usage of the billing profile :uuid
     */
    public function iCalculateTheUsageOfTheBillingProfile($uuid)
    {
        $this->usage = $this->usageTracker->getUsage(Uuid::fromString($uuid), new \DateTime('-1 month'), new \DateTime());
    }

    /**
     * @Then I should see :activeUsers active users
     */
    public function iShouldSeeActiveUsers($activeUsers)
    {

    }

    /**
     * @Given the following usage is recorded for the team :teamSlug:
     */
    public function theFollowingUsageIsRecordedForTheTeam($teamSlug, TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->tracedActivityTracker->track(new UserActivity(
                $teamSlug,
                Uuid::fromString($row['flow_uuid']),
                $row['type'],
                new UserActivityUser($row['user']),
                new \DateTime($row['date'])
            ));
        }
    }

    /**
     * @Given I received the following :type message:
     * @Given I receive the following :type message :count times:
     * @When I receive the following :type message:
     */
    public function iReceiveTheFollowingMessage($type, PyStringNode $string, $count = 1)
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->messageConsumer->execute(new AMQPMessage(
                $string->getRaw(),
                [
                    'application_headers' => [
                        'X-Message-Name' => $type,
                    ],
                ]
            ));
        }
    }

    /**
     * @When I request the activity of the team :teamName between :left and :right
     */
    public function iRequestTheActivityOfTheFlowBetweenAnd($teamName, $left, $right)
    {
        $team = new Team($teamName, $teamName);
        $fromTime = \DateTime::createFromFormat(\DateTime::ISO8601, $left);
        $toTime = \DateTime::createFromFormat(\DateTime::ISO8601, $right);

        $this->activities = $this->tracedActivityTracker->findBy($team, $fromTime, $toTime);
        $this->activitiesCount = $this->tracedActivityTracker->countEventsBy($team, $fromTime, $toTime);
    }

    /**
     * @Then I should see the activity of the user :username
     * @Then I should see the activity of the user :username :count times
     */
    public function iShouldSeeTheActivityOfTheUser($username, $count = 1)
    {
        if ($count != $this->activitiesCount) {
            throw new \RuntimeException(
                sprintf(
                    'Expected to see the user activity %d times, but it occurred %d times.',
                    $count,
                    $this->activitiesCount
                )
            );
        }

        foreach ($this->activities as $activity) {
            if ($activity->getUser()->getUsername() == $username) {
                return;
            }
        }

        throw new \RuntimeException('The activity of that user was not found');
    }

    /**
     * @Then the user activity of the user :username should have been tracked
     */
    public function theUserActivityOfTheUserShouldHaveBeenTracked(string $username)
    {
        $trackedActivities = $this->tracedActivityTracker->getTracked();
        foreach ($trackedActivities as $activity) {
            if ($activity->getUser()->getUsername() == $username) {
                return;
            }
        }

        throw new \RuntimeException('Unable to find this activity');
    }
}
