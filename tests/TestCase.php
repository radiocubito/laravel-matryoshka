<?php

namespace Radiocubito\Matryoshka\Tests;

use Illuminate\Database\Capsule\Manager as DB;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Radiocubito\Matryoshka\Cacheable;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        $this->setUpDatabase();
        $this->migrateTables();
    }

    protected function setUpDatabase()
    {
        $database = new DB;

        $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $database->bootEloquent();
        $database->setAsGlobal();
    }

    protected function migrateTables()
    {
        DB::schema()->create('posts', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();  
        });
    }

    protected function makePost()
    {
        $post = new Post;
        $post->title = 'Some title';
        $post->save();

        return $post; 
    }
}

class Post extends Model
{
    use Cacheable;
}
