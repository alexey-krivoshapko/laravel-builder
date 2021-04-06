<?php

use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation as Relation;

class LaravelQueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        $db = new DB;
        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
        $db->bootEloquent();
        $db->setAsGlobal();
    }

    public function testIsQueryableIsCallable()
    {
        $this->assertTrue(is_callable('QueryBuilder', 'isQueryable'));
    }

    public function testIsQueryableMethodExpectsRelationInstance()
    {
        $isQueryableMethod = new ReflectionMethod(QueryBuilder::class, "isQueryable");
        $isQueryableMethod->setAccessible(true);
        $queryBuilder = new QueryBuilder(Eloquent::getConnectionResolver()->connection());
        $eloquentBuilder = new EloquentBuilder($queryBuilder);
        $testRelation = new TestRelation($eloquentBuilder, new TestModel());
        $result = $isQueryableMethod->invoke($queryBuilder, $testRelation);
        $this->assertTrue($result);
    }
}

class TestRelation extends Relation
{
    public function addConstraints()
    {
        // TODO: Implement addConstraints() method.
    }

    public function addEagerConstraints(array $models)
    {
        // TODO: Implement addEagerConstraints() method.
    }

    public function initRelation(array $models, $relation): array
    {
        return [];
    }

    public function match(array $models, Collection $results, $relation): array
    {
        return [];
    }

    public function getResults()
    {
        // TODO: Implement getResults() method.
    }
}

class TestModel extends Eloquent
{

}