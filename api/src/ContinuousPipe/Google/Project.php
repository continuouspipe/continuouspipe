<?php

namespace ContinuousPipe\Google;

use JMS\Serializer\Annotation as JMS;

final class Project
{
    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("projectId")
     *
     * @var string
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("name")
     *
     * @var string
     */
    private $name;

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
