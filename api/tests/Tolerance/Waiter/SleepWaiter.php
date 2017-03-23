<?php

/*
 * This file is part of the Tolerance package.
 *
 * (c) Samuel ROZE <samuel.roze@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tolerance\Waiter;

/**
 * The purpose of this test implementation is to bypass the sleep method of the original class.
 */
class SleepWaiter implements Waiter
{
    /**
     * {@inheritdoc}
     */
    public function wait($seconds = 1)
    {
    }
}
