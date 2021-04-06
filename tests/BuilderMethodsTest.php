<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\Relation as Relation;
use PHPUnit\Framework\TestCase;

class BuilderMethodsTest extends TestCase
{
    private int $total_posts = 12;

    private array $post_ids = [];

    private array $user_post_ids = [];

    protected function setUp(): void
    {
        $db = new DB;
        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:'
        ]);
        $db->bootEloquent();
        $db->setAsGlobal();
        $this->createSchema();
        $this->seedData();
    }

    protected function tearDown(): void
    {
        $this->schema()->dropAllTables();
    }

    protected function seedData(): void
    {
        $user = User::updateOrCreate([
            'name' => 'User'
        ]);
        $this->user_post_ids = range(1, 10);
        for ($i = 1; $i <= $this->total_posts; $i++) {
            $post = Post::updateOrCreate([
                'title' => "Title $i"
            ]);
            $this->post_ids[] = $post->id;
        };
        $user->posts()->sync($this->user_post_ids);
    }

    public function testEloquentBuilderInSelectToGetPosts(): void
    {
        $result = Post::select(['id' => Post::select('id')->where('id', 2)])->get();
        $this->assertEquals(array_fill(0, $this->total_posts, 2), $result->pluck('id')->toArray());
    }

    public function testQueryBuilderInSelectToGetPosts(): void
    {
        $result = Post::select(['id' => Post::select('id')->where('id', 2)->getQuery()])->get();
        $this->assertEquals(array_fill(0, $this->total_posts, 2), $result->pluck('id')->toArray());
    }

    public function testRelationInSelectToGetPosts(): void
    {
        $user = User::first();
        $result = Post::select(['id' => $user->posts()->select('id')->where('id', 2)])->get();
        $this->assertEquals(array_fill(0, $this->total_posts, 2), $result->pluck('id')->toArray());
    }

    public function testClosureInSelectToGetPosts(): void
    {
        $result = Post::select(['id_from_closure' => function ($query) {
            $query->select('id');
        }])->get();
        $this->assertEquals($this->post_ids, $result->pluck('id_from_closure')->toArray());
    }

    public function testEloquentBuilderInAddSelectToGetPosts(): void
    {
        $result = Post::addSelect(['add_id' => Post::select('id')->where('id', 2)])->get();
        $this->assertEquals(array_fill(0, $this->total_posts, 2), $result->pluck('add_id')->toArray());
    }

    public function testQueryBuilderInAddSelectToGetPosts(): void
    {
        $result = Post::addSelect(['id' => Post::select('id')->where('id', 2)->getQuery()])->get();
        $this->assertEquals(array_fill(0, $this->total_posts, 2), $result->pluck('id')->toArray());
    }

    public function testRelationInAddSelectToGetPosts(): void
    {
        $user = User::first();
        $result = Post::addSelect(['id' => $user->posts()->select('id')->where('id', 2)])->get();
        $this->assertEquals(array_fill(0, $this->total_posts, 2), $result->pluck('id')->toArray());
    }

    public function testClosureInAddSelectToGetPosts(): void
    {
        $result = Post::addSelect(['id_from_closure' => function ($query) {
            $query->select('id');
        }])->get();
        $this->assertEquals($this->post_ids, $result->pluck('id_from_closure')->toArray());
    }

    public function testEloquentBuilderInFromToGetPosts(): void
    {
        $result = Post::from(Post::whereNotNull('id'))->get();
        $this->assertEquals($this->post_ids, $result->pluck('id')->toArray());
    }

    public function testQueryBuilderInFromToGetPosts(): void
    {
        $result = Post::from(Post::whereNotNull('id')->getQuery())->get();
        $this->assertEquals($this->post_ids, $result->pluck('id')->toArray());
    }

    public function testRelationInFromToGetPosts(): void
    {
        $user = User::first();
        $result = Post::from($user->posts())->get();
        $this->assertEquals($this->user_post_ids, $result->pluck('id')->toArray());
    }

    public function testClosureInFromToGetPosts(): void
    {
        $result = Post::from(function ($query) {
            $query->from('posts')->whereNotNull('id');
        })->get();
        $this->assertEquals($this->post_ids, $result->pluck('id')->toArray());
    }

    public function testEloquentBuilderInWhereInToGetPosts(): void
    {
        $result = Post::whereIn('id', Post::select('id'))->get();
        $this->assertEquals($this->post_ids, $result->pluck('id')->toArray());
    }

    public function testQueryBuilderInWhereInToGetPosts(): void
    {
        $result = Post::whereIn('id', Post::select('id')->getQuery())->get();
        $this->assertEquals($this->post_ids, $result->pluck('id')->toArray());
    }

    public function testRelationInWhereInToGetPosts(): void
    {
        $user = User::first();
        $result = Post::whereIn('id', $user->posts()->select('id'))->get();
        $this->assertEquals($this->user_post_ids, $result->pluck('id')->toArray());
    }

    public function testClosureInWhereInToGetPosts(): void
    {
        $result = Post::whereIn('id', function ($query) {
            $query->select('id')->from('posts');
        })->get();
        $this->assertEquals($this->post_ids, $result->pluck('id')->toArray());
    }

    public function createSchema(): void
    {
        $this->schema()->create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
        });

        $this->schema()->create('posts', function ($table) {
            $table->increments('id');
            $table->string('title');
        });

        $this->schema()->create('post_user', function ($table) {
            $table->integer('post_id')->unsigned();
            $table->foreign('post_id')->references('id')->on('posts');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    protected function schema(): object
    {
        return $this->connection()->getSchemaBuilder();
    }

    protected function connection(): object
    {
        return Eloquent::getConnectionResolver()->connection();
    }

}

class Post extends Eloquent
{
    protected $fillable = ['title'];

    public $timestamps = false;
}

class User extends Eloquent
{
    protected $fillable = ['name'];

    public $timestamps = false;

    public function posts(): Relation
    {
        return $this->belongsToMany(Post::class);
    }
}