<?php

namespace spec\ContinuousPipe\River\CodeRepository\Caches;

use ContinuousPipe\River\CodeRepository\Caches\CachedFileSystem;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use Doctrine\Common\Cache\Cache;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CachedFileSystemSpec extends ObjectBehavior
{
    function let(RelativeFileSystem $decorated, Cache $cache)
    {
        $this->beConstructedWith($decorated, $cache, 'key', 3600);
    }

    function it_caches_if_the_file_exists(RelativeFileSystem $decorated, Cache $cache)
    {
        $decorated->exists('path')->willReturn(true)->shouldBeCalledTimes(1);

        $cache->fetch($cacheKey = 'key:exists:'.md5('path'))->willReturn(false);

        $cache->save($cacheKey, ['exists' => true], 3600)->shouldBeCalled()->will(function($args, $cache) {
            $cache->fetch($cacheKey = 'key:exists:'.md5('path'))->willReturn($args[1]);
        });

        $this->exists('path')->shouldReturn(true);
        $this->exists('path')->shouldReturn(true);
    }

    function it_caches_if_the_file_do_not_exists(RelativeFileSystem $decorated, Cache $cache)
    {
        $decorated->exists('not-found-path')->willReturn(false)->shouldBeCalledTimes(1);

        $cache->fetch($cacheKey = 'key:exists:'.md5('not-found-path'))->willReturn(false);
        $cache->save($cacheKey, ['exists' => false], 3600)->shouldBeCalled()->will(function($args, $cache) {
            $cache->fetch($cacheKey = 'key:exists:'.md5('not-found-path'))->willReturn($args[1]);
        });

        $this->exists('not-found-path')->shouldReturn(false);
        $this->exists('not-found-path')->shouldReturn(false);
    }

    function it_discard_the_cache_if_invalid(RelativeFileSystem $decorated, Cache $cache)
    {
        $decorated->exists('path')->willReturn(true)->shouldBeCalledTimes(2);

        $cache->fetch($cacheKey = 'key:exists:'.md5('path'))->willReturn(['something' => 'else']);
        $cache->save($cacheKey, ['exists' => true], 3600)->willReturn(null);

        $this->exists('path')->shouldReturn(true);
        $this->exists('path')->shouldReturn(true);
    }
}
