<?php

namespace Radiocubito\Matryoshka\Tests;

use Exception;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Filesystem\Filesystem;
use Radiocubito\Matryoshka\RussianCaching;
use Radiocubito\Matryoshka\BladeDirective;

class BladeDirectiveTest extends TestCase
{
    protected $doll;

    protected $compiledViewPath;

    protected $viewKey;

    public function setUp(): void
    {
        parent::setUp();

        $this->compiledViewPath = __FILE__;
        $this->viewKey = sha1($this->compiledViewPath . (new Filesystem())->lastModified($this->compiledViewPath));
    }

    /** @test */
    public function it_sets_up_the_opening_cache_directive()
    {
        $directive = $this->createNewCacheDirective();

        $isCached = $directive->setUp($this->compiledViewPath, $post = $this->makePost());

        $this->assertFalse($isCached);

        echo '<div>fragment</div>';

        $cachedFragment = $directive->tearDown();

        $this->assertEquals('<div>fragment</div>', $cachedFragment);
        $this->assertTrue($this->doll->has($post->getCacheKey() . '/' . $this->viewKey));
    }

    /** @test */
    function it_can_use_a_string_as_the_cache_key()
    {
        $doll = $this->prophesize(RussianCaching::class);
        $directive = new BladeDirective($doll->reveal());

        $doll->has('foo/' . $this->viewKey)->shouldBeCalled();
        $directive->setUp($this->compiledViewPath, 'foo');

        ob_end_clean(); // Since we're not doing teardown.
    }

    /** @test */
    function it_can_use_a_collection_as_the_cache_key()
    {
        $doll = $this->prophesize(RussianCaching::class);
        $directive = new BladeDirective($doll->reveal());
        
        $collection = collect(['one', 'two']);
        $doll->has(sha1($collection) . '/' . $this->viewKey)->shouldBeCalled();
        $directive->setUp($this->compiledViewPath, $collection);

        ob_end_clean(); // Since we're not doing teardown.
    }

    /** @test */
    function it_can_use_the_model_to_determine_the_cache_key()
    {
        $doll = $this->prophesize(RussianCaching::class);
        $directive = new BladeDirective($doll->reveal());

        $post = $this->makePost(); 
        $doll->has('Radiocubito\Matryoshka\Tests\Post/1-' . $post->updated_at->timestamp . '/' . $this->viewKey)->shouldBeCalled();
        $directive->setUp($this->compiledViewPath, $post);

        ob_end_clean(); // Since we're not doing teardown.
    }

    /** @test */
    function it_can_use_a_string_to_override_the_models_cache_key()
    {
        $doll = $this->prophesize(RussianCaching::class);
        $directive = new BladeDirective($doll->reveal());

        $doll->has('override-key' . '/' . $this->viewKey)->shouldBeCalled();
        $directive->setUp($this->compiledViewPath, $this->makePost(), 'override-key');

        ob_end_clean(); // Since we're not doing teardown.
    }


    /** @test */
    function it_throws_an_exception_if_it_cannot_determine_the_cache_key()
    {
        $this->expectException(Exception::class);

        $directive = $this->createNewCacheDirective();

        $directive->setUp($this->compiledViewPath, new UnCacheablePost);
    }

    protected function createNewCacheDirective()
    {
        $cache = new Repository(
            new ArrayStore()
        );

        $this->doll = new RussianCaching($cache);

        return new BladeDirective($this->doll);
    }
}

class UnCacheablePost extends \Illuminate\Database\Eloquent\Model {}
