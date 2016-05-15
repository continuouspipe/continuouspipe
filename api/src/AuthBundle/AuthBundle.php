<?php

namespace AuthBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AuthBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'HWIOAuthBundle';
    }
}
