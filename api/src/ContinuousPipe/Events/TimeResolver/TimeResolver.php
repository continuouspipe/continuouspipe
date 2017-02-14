<?php

namespace ContinuousPipe\Events\TimeResolver;

interface TimeResolver
{
    public function resolve() : \DateTimeInterface;
}
