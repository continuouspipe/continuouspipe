<?php
/**
 * Created by PhpStorm.
 * User: galzsolt
 * Date: 13/03/2017
 * Time: 15:10
 */

namespace AppBundle\Entity;

use JMS\Serializer\Annotation as JMS;

class TeamLimitations
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $tidesPerHour;

    public function __construct(int $tidesPerHour)
    {
        $this->tidesPerHour = $tidesPerHour;
    }

    /**
     * @return int
     */
    public function getTidesPerHour(): int
    {
        return $this->tidesPerHour;
    }
}