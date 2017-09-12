<?php

namespace ContinuousPipe\River\Managed\Resources\Calculation;

class Interval
{
    /**
     * @var \DateTime
     */
    private $left;

    /**
     * @var \DateTime
     */
    private $right;

    public function __construct(\DateTime $left, \DateTime $right)
    {
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * @return \DateTime
     */
    public function getLeft(): \DateTime
    {
        return $this->left;
    }

    /**
     * @return \DateTime
     */
    public function getRight(): \DateTime
    {
        return $this->right;
    }

    /**
     * Call the `$callable` for each `$internal` within the interval.
     *
     * @param \DateInterval $interval
     * @param callable $callable
     *
     * @return mixed[]
     */
    public function forEachInterval(\DateInterval $interval, callable $callable)
    {
        $results = [];
        $cursor = $this->left;

        while ($cursor < $this->right) {
            $cursorEnd = clone $cursor;
            $cursorEnd->add($interval);

            $results[] = $callable($cursor, $cursorEnd);

            // Move cursor
            $cursor = $cursorEnd;
        }

        return $results;
    }
}
