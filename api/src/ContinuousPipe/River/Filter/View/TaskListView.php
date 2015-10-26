<?php

namespace ContinuousPipe\River\Filter\View;

class TaskListView
{
    /**
     * @var array
     */
    private $tasks;

    /**
     * @param array $tasks
     */
    public function __construct(array $tasks = [])
    {
        $this->tasks = $tasks;
    }

    /**
     * @param string $id
     * @param mixed  $view
     */
    public function add($id, $view)
    {
        $this->tasks[$id] = $view;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->tasks)) {
            throw new \InvalidArgumentException(sprintf(
                'Task "%s" do not exists',
                $key
            ));
        }

        return $this->tasks[$key];
    }
}
