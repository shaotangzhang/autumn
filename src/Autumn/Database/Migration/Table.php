<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/05/2024
 */

namespace Autumn\Database\Migration;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;
use Autumn\Database\Db;
use Autumn\Database\DbConnection;
use Autumn\Database\DbException;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Database\Models\AbstractEntity;
use Autumn\Exceptions\SystemException;
use Autumn\Exceptions\ValidationException;
use Psr\Log\LoggerInterface;

class Table
{
    public const DEFAULT_CHARSET = 'utf8mb4';

    private const TYPE_SORT = [
        'id' => Column::PRIORITY_PK,
        'bigint' => Column::PRIORITY_FK,
        'varchar' => Column::PRIORITY_STRING,
        'char' => Column::PRIORITY_CHAR,
        'integer' => Column::PRIORITY_TIMESTAMPS - 100,
        'mediumint' => Column::PRIORITY_TIMESTAMPS - 99,
        'int' => Column::PRIORITY_TIMESTAMPS - 98,
        'smallint' => Column::PRIORITY_TIMESTAMPS - 97,
        'tinyint' => Column::PRIORITY_TIMESTAMPS - 96,
        'bit' => Column::PRIORITY_TIMESTAMPS - 95,
        'bool' => Column::PRIORITY_TIMESTAMPS - 94,
        'boolean' => Column::PRIORITY_TIMESTAMPS - 93,
        'float' => Column::PRIORITY_TIMESTAMPS - 92,
        'decimal' => Column::PRIORITY_TIMESTAMPS - 91,
        'double' => Column::PRIORITY_TIMESTAMPS - 90,
        'year' => Column::PRIORITY_TIMESTAMPS - 4,
        'date' => Column::PRIORITY_TIMESTAMPS - 3,
        'datetime' => Column::PRIORITY_TIMESTAMPS - 2,
        'time' => Column::PRIORITY_TIMESTAMPS - 1,
        'timestamp' => Column::PRIORITY_TIMESTAMPS,
        'json' => Column::PRIORITY_TEXT + 20,
        'geometry' => Column::PRIORITY_TEXT + 21,
        'enum' => Column::PRIORITY_TEXT + 22,
        'set' => Column::PRIORITY_TEXT + 23,
        'text' => Column::PRIORITY_TEXT,
        'tinytext' => Column::PRIORITY_TEXT + 1,
        'mediumtext' => Column::PRIORITY_TEXT + 2,
        'longtext' => Column::PRIORITY_TEXT + 3,
        'binary' => Column::PRIORITY_TEXT + 28,
        'varbinary' => Column::PRIORITY_TEXT + 29,
        'blob' => Column::PRIORITY_TEXT + 30,
        'tinyblob' => Column::PRIORITY_TEXT + 31,
        'mediumblob' => Column::PRIORITY_TEXT + 32,
        'longblob' => Column::PRIORITY_TEXT + 33,
        'point' => Column::PRIORITY_TEXT + 34,
        'multipoint' => Column::PRIORITY_TEXT + 35,
        'linestring' => Column::PRIORITY_TEXT + 36,
        'multilinestring' => Column::PRIORITY_TEXT + 37,
        'polygon' => Column::PRIORITY_TEXT + 38,
        'multipolygon' => Column::PRIORITY_TEXT + 39,
        'geometrycollection' => Column::PRIORITY_TEXT + 40,
    ];

    private array $parameters = [];

    private ?LoggerInterface $logger = null;

    public function __construct(private readonly string $entityClass, private ?DbConnection $connection = null)
    {
        if (!is_subclass_of($this->entityClass, EntityInterface::class)) {
            throw ValidationException::of('Invalid entity class: %s', $this->entityClass);
        }
    }

    /**
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface|null $logger
     */
    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return DbConnection|null
     */
    public function getConnection(): ?DbConnection
    {
        return $this->connection;
    }

    /**
     * @throws DbException
     */
    public function up(): void
    {
        $table = Db::entity_name($this->entityClass);

        $this->connection ??= Db::forEntity($this->entityClass);

        if (!$this->connection) {
            throw SystemException::of('No connection configured to the entity: %s', $this->entityClass);
        }

        if ($count = $this->connection->exists($table)) {
            return;
        }

        if ($count === 0) {
            $this->dropTable($table);
        }

        $this->createTable($table, ...array_values($this->getTableColumns($this->entityClass)));
        $this->createTableIndexes($table, ...array_values($this->getTableIndexes($this->entityClass)));
    }

    public function down(): void
    {
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getTableColumns(string $class): array
    {
        if (is_subclass_of($class, AbstractEntity::class)) {
            return $class::entity_columns();
        }

        return [];
    }

    public function getTableIndexes(string $class): array
    {
        if (is_subclass_of($class, AbstractEntity::class)) {
            return $class::entity_indexes();
        }

        return [];
    }

    /**
     * @throws DbException
     */
    protected function createTable(string $table, Column ...$columns): void
    {
        $this->parameters = [];

        $sql = [];

        // 生成列的定义
        foreach ($this->sortColumns(...$columns) as $column) {
            $sql[$column->getName()] ??= $this->defineColumn($column);
        }

        // 将列和索引的定义组合成 CREATE TABLE 语句
        $lines = implode(',' . PHP_EOL, array_values($sql));
        $sql = "CREATE TABLE IF NOT EXISTS $table (\n$lines\n)";

        $result = Db::execute($sql, $this->parameters);
        $this->log(__FUNCTION__, $sql, null, $result);
    }

    public function createTableIndexes(string $table, mixed ...$indexes): void
    {
        $this->parameters = [];

        $sql = implode(' ', ['ALTER TABLE', $table]);

        foreach ($indexes as $index) {
            if ($indexSQL = $this->defineIndex($index, $this->entityClass)) {
                try {
                    $result = Db::execute($sql . ' ADD ' . $indexSQL, $this->parameters);
                } catch (DbException $e) {
                    $result = $e->getMessage();
                }
                $this->log(__FUNCTION__, $indexSQL, null, $result);
            }
        }
    }

    /**
     * @throws DbException
     */
    protected function createTableFromEntity(string|AbstractEntity $entity): void
    {
        $this->createTable($table = $entity::entity_name(), ...$entity::entity_columns());
    }

    public function sortColumns(Column ...$columns): array
    {

        usort($columns, function (Column $a, Column $b) {
            $typeOrder = static::TYPE_SORT;
            $result = $a->getPriority() <=> $b->getPriority();
            if ($result === 0) {
                $result = ($typeOrder[$a->getType()] ?? 100) <=> ($typeOrder[$b->getType()] ?? 100);
                if ($result === 0) {
                    $result = $a->isUnsigned() <=> $b->isUnsigned();
                    if ($result === 0) {
                        $result = ($a->getCharset() ?: static::DEFAULT_CHARSET) <=> ($b->getCharset() ?: static::DEFAULT_CHARSET);
                        if ($result === 0) {
                            $result = $a->getSize() <=> $b->getSize();
                            if ($result === 0) {
                                $result = $a->isCurrentTimestampOnCreate() <=> $b->isCurrentTimestampOnCreate();
                                if ($result === 0) {
                                    $result = $a->isCurrentTimestampOnUpdate() <=> $b->isCurrentTimestampOnUpdate();
                                }
                            }
                        }
                    }
                }
            }

            return $result;
        });

        return $columns;
    }

    public function defineColumn(Column $column, string $class = null): string
    {
        $sql = [$column->getName(), $type = strtolower($column->getDataType())];

        if ($size = $column->getSize()) {
            if ($precision = $column->getPrecision()) {
                $sql[] = "($size,$precision)";
            } else {
                $sql[] = "($size)";
            }
        }

        if ($charset = $column->getCharset()) {
            $sql[] = "CHARACTER SET $charset";
        }

        if ($collate = $column->getCollate()) {
            $sql[] = "COLLATE $collate";
        }

        if ($column->isNotNull()) {
            $sql[] = 'NOT NULL';
        }

        if ($canHasDefault = !$column->isAuto()) {
            foreach (['blob', 'text', 'geometry', 'json'] as $key) {
                if (str_contains($type, $key)) {
                    $canHasDefault = false;
                    break;
                }
            }
        }

        if ($canHasDefault) {
            $default = $column->getDefault();
            if ($default !== null) {
                if (is_bool($default)) {
                    $param = $default ? 'true' : 'false';
                    $sql[] = "DEFAULT $param NOT NULL";
                } else {
                    $paramName = $this->bind($default);
                    $sql[] = "DEFAULT :$paramName NOT NULL";
                }
            } else {
                if ($column->getNullable() === true) {
                    $sql[] = 'DEFAULT NULL';
                } elseif ($column->isCurrentTimestampOnCreate()) {
                    $sql[] = 'DEFAULT CURRENT_TIMESTAMP';
                }

                if ($column->isCurrentTimestampOnUpdate()) {
                    $sql[] = 'ON UPDATE CURRENT_TIMESTAMP';
                }
            }
        }

        $primaryKey = call_user_func([$class ?: $this->entityClass, 'column_primary_key']) ?: 'id';

        if ($column->isAuto() || ($primaryKey === $column->getName())) {
            $sql[] = 'AUTO_INCREMENT PRIMARY KEY';
        }

        if ($comment = $column->getComment()) {
            $paramName = $this->bind($comment);
            $sql[] = "COMMENT :$paramName";
        }

        return implode(' ', $sql);
    }

    private function bind(mixed $value): string
    {
        static $n = 0;
        $paramName = 'P_' . $n++;
        $this->parameters[$paramName] = $value;
        return $paramName;
    }

    protected function defineIndex(Index $index, string $class): string
    {
        $columns = [];

        $tableColumns = $this->getTableColumns($class);

        foreach ($index->getColumns() as $column) {
            $columnName = null;

            if ($col = $tableColumns[$column] ?? null) {
                $columns[] = $col->getName();
            } elseif (is_callable($func = $class . '::entity_' . $column . '_column')) {
                $columnName = call_user_func($func);
            } else {
                foreach ($tableColumns as $col) {
                    if ($column === $col->getName()) {
                        $columnName = $column;
                        break;
                    }
                }
            }

            if ($columnName) {
                $columns[] = $columnName;
            }
        }

        if (count($columns)) {
            $columnList = implode(',', $columns);
            $indexType = $index->getType();
            $indexName = $index->getName();
            if (!$indexName) {
                $indexName = strtr($columnList, ',', '_');
                $indexName = strtolower(substr($indexType, 0, 1)) . 'dx_'
                    . substr($indexName, 0, 16) . '_'
                    . substr(md5($indexName), 24, 8);
            }

            return sprintf("%s %s (%s)", $indexType, $indexName, $columnList);
        }

        return '';
    }

    /**
     * @param string $name
     * @throws DbException
     */
    protected function dropTable(string $name): void
    {
        $sql = "DROP TABLE IF EXISTS $name";
        $result = Db::execute($sql);
        $this->log(__FUNCTION__, $sql, null, $result);
    }

    /**
     * @param string $table
     * @param string $column
     * @throws DbException
     */
    protected function dropColumn(string $table, string $column): void
    {
        $sql = "ALTER TABLE $table DROP COLUMN $column";
        $result = Db::execute($sql);
        $this->log(__FUNCTION__, $sql, null, $result);
    }

    public function log(string $method, string $sql, array $args = null, mixed $result = null): void
    {
        $this->logger?->info(implode("\t", func_get_args()));
    }

}