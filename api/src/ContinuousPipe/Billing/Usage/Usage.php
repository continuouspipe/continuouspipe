<?php

namespace ContinuousPipe\Billing\Usage;

class Usage
{
    /**
     * @var int
     */
    private $numberOfActiveUsers;

    /**
     * @param int $numberOfActiveUsers
     */
    public function __construct(int $numberOfActiveUsers)
    {
        $this->numberOfActiveUsers = $numberOfActiveUsers;
    }

    /**
     * @return int
     */
    public function getNumberOfActiveUsers(): int
    {
        return $this->numberOfActiveUsers;
    }
}
