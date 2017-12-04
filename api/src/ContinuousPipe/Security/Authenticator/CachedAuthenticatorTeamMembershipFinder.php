<?php

namespace ContinuousPipe\Security\Authenticator;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipFinder;
use ContinuousPipe\Security\User\User;
use Doctrine\Common\Cache\Cache;

/**
 * Cache decorator for AuthenticatorTeamMembershipFinder class.
 */
class CachedAuthenticatorTeamMembershipFinder implements TeamMembershipFinder
{
    /**
     * @var AuthenticatorTeamMembershipFinder
     */
    private $membershipFinder;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var int
     */
    private $lifetime;

    public function __construct(AuthenticatorTeamMembershipFinder $membershipFinder, Cache $cache, $lifetime = 1600)
    {
        $this->membershipFinder = $membershipFinder;
        $this->cache = $cache;
        $this->lifetime = $lifetime;
    }

    /**
     * Find team membership by team and user.
     *
     * @param Team $team
     * @param User $user
     *
     * @return TeamMembership|null
     */
    public function findOneByTeamAndUser(Team $team, User $user)
    {
        $cacheKey = sprintf('membership_%s_%s', $team->getSlug(), $user->getUsername());
        if (false === ($membership = $this->get($cacheKey))) {
            $membership = $this->membershipFinder->findOneByTeamAndUser($team, $user);
            $this->set($cacheKey, $membership);
        }
        return $membership;
    }

    /**
     * Get the cached value by its ID.
     *
     * @param string $key
     *
     * @return bool|mixed
     */
    private function get($key)
    {
        if (false !== ($value = $this->cache->fetch($key))) {
            return unserialize($value);
        }

        return false;
    }

    /**
     * Store the given object to cache.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->cache->save($key, serialize($value), $this->lifetime);
    }
}
