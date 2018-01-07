<?php

namespace Helpers;

trait FixturesHelper
{
    /**
     * @param string $fixture
     *
     * @return string
     */
    private function loadFixture(string $fixture, string $context): string
    {
        return file_get_contents(__DIR__ . '/../../'.$context.'/fixtures/' . $fixture);
    }
}
