<?php

namespace AppBundle\Command;

use ContinuousPipe\Billing\ActivityTracker\ActivityTracker;
use ContinuousPipe\Message\UserActivity;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Predis\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ContinuousPipe\Billing\Infrastructure\Doctrine\Entity\UserActivity as UserActivityEntity;

class MigrateUserActivitiesCommand extends Command
{
    /**
     * @var ClientInterface
     */
    private $redisClient;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ActivityTracker
     */
    private $activityTracker;


    public function __construct(
        $name = null,
        ClientInterface $redisClient,
        SerializerInterface $serializer,
        ActivityTracker $activityTracker
    ) {
        parent::__construct($name);

        $this->redisClient = $redisClient;
        $this->serializer = $serializer;
        $this->activityTracker = $activityTracker;
    }

    protected function configure()
    {
        $this->setDescription(
            'Migrate user activity data from Redis storage into MySQL. Data in Redis will be preserved.'
        );

        $this->addOption('run', null, null, 'Start the migration process.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('run')) {
            $count = $this->migrateData();

            $output->writeln(sprintf('%d number of entities has been migrated.', $count));
            return;
        }


        $output->writeln(
            sprintf(
                'There are %d entries in Redis. To start the migration process use the --run switch.',
                count($this->loadActivitiesFromRedisStorage())
            )
        );
    }

    private function migrateData()
    {
        $createdEntitiesCount = 0;
        foreach ($this->loadActivitiesFromRedisStorage() as $userActivity) {
            $this->activityTracker->track($userActivity);
            ++$createdEntitiesCount;
        }

        return $createdEntitiesCount;
    }

    private function loadActivitiesFromRedisStorage()
    {
        $keys = $this->redisClient->keys('activity:*');
        $activities = [];

        foreach ($keys as $key) {
            $message = $this->redisClient->get($key);

            try {
                $activities[] = $this->serializer->deserialize($message, UserActivity::class, 'json');
            } catch (\Exception $e) {
            }
        }

        return $activities;
    }
}
