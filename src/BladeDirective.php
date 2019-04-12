<?php

namespace Radiocubito\Matryoshka;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class BladeDirective
{
    /**
     * The cache instance.
     *
     * @var RussianCaching
     */
    protected $cache;

    /**
     * A list of model cache keys.
     *
     * @param array $keys
     */
    protected $keys = [];

    /**
     * Create a new instance.
     *
     * @param RussianCaching $cache
     */
    public function __construct(RussianCaching $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Handle the @cache setup.
     *
     * @param string $compiledViewPath
     * @param mixed       $model
     * @param string|null $key
     */
    public function setUp(string $compiledViewPath, $model, string $key = null)
    {
        ob_start();

        $this->keys[] = $key = $this->normalizeKey($compiledViewPath, $model, $key);

        return $this->cache->has($key);
    }

    /**
     * Handle the @endcache teardown.
     */
    public function tearDown()
    {
        return $this->cache->put(
            array_pop($this->keys), ob_get_clean()
        );
    }

    /**
     * Normalize the cache key.
     */
    protected function normalizeKey($compiledViewPath, $item, $key = null)
    {
        $viewKey = sha1((new Filesystem())->lastModified($compiledViewPath));

        // If the user wants to provide their own cache
        // key, we'll opt for that.
        if (is_string($item) || is_string($key)) {
            return sprintf("%s/%s",
                is_string($item) ? $item : $key,
                $viewKey,
            );
        }
        
        // Otherwise we'll try to use the item to calculate
        // the cache key, itself.
        if (is_object($item) && method_exists($item, 'getCacheKey')) {
            return sprintf("%s/%s",
                $item->getCacheKey(),
                $viewKey,
            );
        }
    
        // If we're dealing with a collection, we'll 
        // use a hashed version of its contents.
        if ($item instanceof Collection) {
            return sprintf("%s/%s",
                sha1($item),
                $viewKey,
            );
        }

        ob_end_clean();

        throw new Exception('Could not determine an appropriate cache key.');
    }
}
