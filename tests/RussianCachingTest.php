<?php

namespace Radiocubito\Matryoshka\Tests;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Radiocubito\Matryoshka\RussianCaching;

class RussianCachingTest extends TestCase
{
    /** @test */
    public function it_caches_the_given_key()
    {
        $post = $this->makePost();
    
        $cache = new Repository(
            new ArrayStore()
        );

        $cache = new RussianCaching($cache);

        $fragment = '<div>view fragment</div>';

        $cache->put($post, $fragment);

        $this->assertTrue($cache->has($post->getCacheKey()));
        $this->assertTrue($cache->has($post));
    }
    
}

