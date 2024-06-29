<?php

namespace Autumn\Database\Models;

use Autumn\Database\Db;
use Autumn\Database\DbConnection;
use Autumn\Database\DbResultSet;
use Autumn\Exceptions\NotFoundException;
use Autumn\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/../../../../boot.php';

class SomeEntityClass extends AbstractEntity
{
    // Implement necessary methods for EntityInterface
    public static function entity_name(): string
    {
        return 'some_entities';
    }
}

class RepositoryTest extends TestCase
{
    private string $modelClass;
    private DbConnection $connection;

    protected function setUp(): void
    {
        $this->modelClass = SomeEntityClass::class; // Replace with a valid EntityInterface implementation
        $this->connection = $this->createMock(DbConnection::class);
    }

    public function testConstructorWithInvalidEntity()
    {
        $this->expectException(ValidationException::class);
        Repository::of('InvalidEntityClass');
    }

    public function testConstructorWithValidEntity()
    {
        $repository = new Repository($this->modelClass, [], $this->connection);
        $this->assertInstanceOf(Repository::class, $repository);
    }

    public function testFirstReturnsNull()
    {
        $repository = new Repository($this->modelClass, [], $this->connection);
        $repository = $repository->reset(); // Ensure the query is reset

        $this->connection->method('query')
            ->willReturn($this->createMock(DbResultSet::class));

        $result = $repository->first();
        $this->assertNull($result);
    }

    public function testFirstOrFailThrowsNotFoundException()
    {
        $repository = new Repository($this->modelClass, [], $this->connection);
        $repository = $repository->reset(); // Ensure the query is reset

        $this->connection->method('query')
            ->willReturn($this->createMock(DbResultSet::class));

        $this->expectException(NotFoundException::class);
        $repository->firstOrFail();
    }

    public function testReset()
    {
        $repository = new Repository($this->modelClass, [], $this->connection);
        $repository->alias('SOME ALIAS');
        $repository->bindValue('abc');

        $this->assertNotEmpty($repository->parameters());
        $this->assertNotEmpty($repository->aliasName());

        $repository = $repository->reset();

        $this->assertEmpty($repository->aliasName());
        $this->assertEmpty($repository->parameters());

        $this->assertEquals(Db::PARAMETER_PREFIX . '0', $repository->bindValue('abc'));
    }

    public function testCountReturnsZero()
    {
        $repository = new Repository($this->modelClass, [], $this->connection);
        $repository = $repository->reset(); // Ensure the query is reset

        $mockResultSet = $this->createMock(DbResultSet::class);
        $mockResultSet->method('fetchColumn')->willReturn(0);

        $this->connection->method('query')->willReturn($mockResultSet);

        $count = $repository->count();
        $this->assertEquals(0, $count);
    }

    public function testExistsReturnsFalse()
    {
        $repository = new Repository($this->modelClass, [], $this->connection);
        $repository = $repository->reset(); // Ensure the query is reset

        $mockResultSet = $this->createMock(DbResultSet::class);
        $mockResultSet->method('fetchColumn')->willReturn(false);

        $this->connection->method('query')->willReturn($mockResultSet);

        $exists = $repository->exists();
        $this->assertFalse($exists);
    }

    // Add more tests to cover other methods

//    public function testAggregateCount()
//    {
//        $repository = new Repository($this->modelClass, [], $this->connection);
//        $repository = $repository->reset(); // Ensure the query is reset
//
//        $mockResultSet = $this->createMock(DbResultSet::class);
//        $mockResultSet->method('fetchColumn')->willReturn(10);
//
//        $this->connection->method('query')->willReturn($mockResultSet);
//
//        $count = $repository->count();
//        $this->assertEquals(10, $count);
//    }

//    public function testOrderBy()
//    {
//        $repository = new Repository($this->modelClass, [], $this->connection);
//        $repository = $repository->reset(); // Ensure the query is reset
//
//        $repository = $repository->orderBy('name', true);
//
//        // Simulate the query method to check if the orderBy was added correctly
//        $this->assertStringContainsString('ORDER BY name DESC', $repository->query()->getQueryString());
//    }

//    public function testLimit()
//    {
//        $repository = new Repository($this->modelClass, [], $this->connection);
//        $repository = $repository->reset(); // Ensure the query is reset
//
//        $this->connection->method('query')
//            ->willReturn($resultSet = $this->createMock(DbResultSet::class));
//        $resultSet->method('getQueryString')->willReturn('SELECT * FROM some_entities LIMIT 10');
//
//        $repository = $repository->limit(10);
//
//        // Simulate the query method to check if the limit was added correctly
//        $this->assertStringContainsString('LIMIT 10', $repository->query()->getQueryString());
//    }
}