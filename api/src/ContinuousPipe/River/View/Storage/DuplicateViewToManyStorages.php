<?php

namespace ContinuousPipe\River\View\Storage;

use ContinuousPipe\River\View\Tide;

final class DuplicateViewToManyStorages implements TideViewStorage
{
    /**
     * @var array|TideViewStorage[]
     */
    private $storages;

    /**
     * @param TideViewStorage[] $storages
     */
    public function __construct(array $storages)
    {
        $this->storages = $storages;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Tide $tide)
    {
        foreach ($this->storages as $storage) {
            $storage->save($tide);
        }
    }
}
