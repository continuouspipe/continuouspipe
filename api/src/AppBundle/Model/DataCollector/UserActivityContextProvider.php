<?php

namespace AppBundle\Model\DataCollector;


use ContinuousPipe\UserActivity\UserActivityContext;

interface UserActivityContextProvider
{
    public function getContext(): UserActivityContext;
}