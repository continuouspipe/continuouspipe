<?php

namespace ContinuousPipe\HealthChecker;

use JMS\Serializer\Annotation as JMS;

final class Problem
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $category;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $message;

    /**
     * @param string $category
     * @param string $message
     */
    public function __construct(string $category, string $message)
    {
        $this->category = $category;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
