<?php

namespace Builder;

use Docker\Context\ContextInterface;

interface Archive extends ContextInterface
{
    public function getContents();
}
