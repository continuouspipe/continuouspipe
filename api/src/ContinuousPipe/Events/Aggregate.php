<?php

namespace ContinuousPipe\Events;

interface Aggregate
{
    public function raisedEvents() : array;
}
