<?php

namespace ContinuousPipe\River\Tide\Concurrency;

final class StartingTideRecommendation
{
    /**
     * @var \DateTime|null
     */
    private $postPoneTo;

    /**
     * @var string|null
     */
    private $reason;

    private function __construct(\DateTime $postPoneTo = null, string $reason = null)
    {
        $this->postPoneTo = $postPoneTo;
        $this->reason = $reason;
    }

    public static function postponeTo(\DateTime $dateTime, string $reason)
    {
        return new self($dateTime, $reason);
    }

    public static function runNow()
    {
        return new self();
    }

    public function shouldPostpone() : bool
    {
        return null !== $this->postPoneTo;
    }

    public function shouldPostponeTo() : \DateTime
    {
        return $this->postPoneTo ?: new \DateTime();
    }

    public function reason() : string
    {
        return $this->reason ?: '';
    }
}
