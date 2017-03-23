<?php

namespace GitHub\Integration;

interface InstallationRepositoryWithCacheInvalidation
{
    /**
     * Invalidate all cache entries associated with the given integration installation.
     *
     * @param Installation $installation
     *
     * @return void
     */
    public function invalidate(Installation $installation);
}
