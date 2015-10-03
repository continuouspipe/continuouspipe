<?php

namespace GitHub\WebHook\Model;

use JMS\Serializer\Annotation as JMS;

class Branch
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("GitHub\WebHook\Model\CommitReference")
     *
     * @var CommitReference
     */
    private $commit;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return CommitReference
     */
    public function getCommit()
    {
        return $this->commit;
    }
}
