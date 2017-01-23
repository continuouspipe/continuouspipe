<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use ContinuousPipe\Billing\ActivityTracker\TracedActivityTracker;
use ContinuousPipe\Message\UserActivity;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Ramsey\Uuid\Uuid;

class BillingContext implements Context
{
    private $messageConsumer;
    private $tracedActivityTracker;

    /**
     * @var UserActivity[]
     */
    private $activities;

    public function __construct(ConsumerInterface $messageConsumer, TracedActivityTracker $tracedActivityTracker)
    {
        $this->messageConsumer = $messageConsumer;
        $this->tracedActivityTracker = $tracedActivityTracker;
    }

    /**
     * @Given I received the following :type message:
     * @When I receive the following :type message:
     */
    public function iReceiveTheFollowingMessage($type, PyStringNode $string)
    {
        $this->messageConsumer->execute(new AMQPMessage(
            $string->getRaw(),
            [
                'application_headers' => [
                    'X-Message-Name' => $type,
                ],
            ]
        ));
    }

    /**
     * @When I request the activity of the flow :flowUuid between :left and :right
     */
    public function iRequestTheActivityOfTheFlowBetweenAnd($flowUuid, $left, $right)
    {
        $this->activities = $this->tracedActivityTracker->findBy(
            Uuid::fromString($flowUuid),
            \DateTime::createFromFormat(\DateTime::ISO8601, $left),
            \DateTime::createFromFormat(\DateTime::ISO8601, $right)
        );
    }

    /**
     * @Then I should see the activity of the user :username
     */
    public function iShouldSeeTheActivityOfTheUser($username)
    {
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
