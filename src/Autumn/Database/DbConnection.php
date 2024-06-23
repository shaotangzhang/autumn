<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database;

use Autumn\Database\Interfaces\DriverInterface;
use Autumn\Traits\ContextInterfaceTrait;
use Autumn\Traits\HasProperties;

class DbConnection
{
    use HasProperties;
    use ContextInterfaceTrait;

    private string $suffix;
    private ?DriverInterface $driver = null;

    public function __construct(private readonly ?string $name = null, private readonly array $options = [])
    {
        $this->suffix = strtoupper($this->name ? "_$this->name" : '');
    }

    public function getDriver(): DriverInterface
    {
        if (!$this->driver) {
            $type = $this->getType();
            $class = __NAMESPACE__ . '\\Drivers\\' . ucfirst($type ?: Db::DEFAULT_DRIVER) . '\\Driver';
            if (!is_subclass_of($class, DriverInterface::class)) {
                throw new \RuntimeException("Invalid connection driver `$type` in configuration.");
            }

            $this->driver = new $class($this);
        }

        return $this->driver;
    }

    /**
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function getType(): ?string
    {
        return $this->properties['type'] ??= env('DB_TYPE' . $this->suffix);
    }

    public function getHost(): ?string
    {
        return $this->properties['host'] ??= env('DB_HOST' . $this->suffix);
    }

    public function getPort(): ?string
    {
        return $this->properties['port'] ??= env('DB_PORT' . $this->suffix);
    }

    public function getUser(): ?string
    {
        return $this->properties['user'] ??= env('DB_USER' . $this->suffix);
    }

    public function getPass(): ?string
    {
        return $this->properties['pass'] ??= env('DB_PASS' . $this->suffix);
    }

    public function getDatabase(): ?string
    {
        return $this->properties['database'] ??= env('DB_DATABASE' . $this->suffix) ?: env('DB_NAME' . $this->suffix);
    }

    public function getCharset(): ?string
    {
        return $this->properties['charset'] ??= env('DB_CHARSET' . $this->suffix);
    }

    public function getPrefix(): ?string
    {
        return $this->properties['prefix'] ??= env('DB_PREFIX' . $this->suffix);
    }

    public function getEngine(): ?string
    {
        return $this->properties['engine'] ??= env('DB_ENGINE' . $this->suffix);
    }

    public function getSocket(): ?string
    {
        return $this->properties['socket'] ??= env('DB_SOCKET' . $this->suffix);
    }

    public function query(string $sql, array $parameters = null): DbResultset
    {
        return new DbResultSet($this->getDriver(), $sql, $parameters);
    }

    /**
     * @throws DbException
     */
    public function execute(string $sql, array $parameters = null): ?int
    {
        return $this->executeToInsert($sql, $parameters);
    }

    /**
     * @throws DbException
     */
    public function executeToInsert(string $sql, array $parameters = null, mixed &$insertedId = null): ?int
    {
        if ($driver = $this->getDriver()) {
            if ($result = $driver->exec($sql, $parameters ?? [])) {
                if (func_num_args() > 2) {
                    $insertedId = $driver->getInsertedId($result);
                }
                return $driver->getAffectedRows($result);
            }

            if ($exception = $driver->getException()) {
                throw $exception;
            }
        }

        return null;
    }

    public function where(array $conditions, array &$parameters = null, int &$paramIndex = 0, string $paramPrefix = null, string $connector = null): array
    {
        $sql = [];

        $connector ??= 'AND';
        foreach ($conditions as $name => $value) {
            if (is_int($name)) {
                if (is_array($value)) {
                    $parts = $this->where($value, $parameters, $paramIndex, $paramPrefix, ($connector === 'OR') ? 'AND' : 'OR');
                    $value = implode(" $connector ", $parts);
                }

                if ($value !== null && $value !== '') {
                    $sql[] = $value;
                }
            } else {
                $param = ($paramPrefix ?? 'P_') . $paramIndex++;
                $parameters[$param] = $value;
                $sql[] = "$name = :$param";
            }
        }

        return $sql;
    }

    public function insert(string $table, array $data, bool $ignore = null): int|string
    {
        if (empty($data)) {
            return 0;
        }

        $keys = array_keys($data);

        $sql = [
            'INSERT', $ignore ? 'IGNORE INTO' : 'INTO', $table,
            '(', implode(',', $keys), ') VALUES',
            '(', implode(',', array_map(fn($v) => ":$v", $keys)), ')'
        ];

        $sql = implode(' ', $sql);

        return $this->getDriver()->insert($sql, $data);
    }

    /**
     * @throws DbException
     */
    public function update(string $table, array $data, string|array $conditions = null): int
    {
        if (empty($data)) {
            return 0;
        }

        $sql = [];
        $parameters = [];
        $paramIndex = 0;
        $paramPrefix = 'P_';
        foreach ($data as $name => $value) {
            $param = $paramPrefix . $paramIndex++;
            $parameters[$param] = $value;
            $sql[] = "$name=:$param";
        }

        $sql = ['UPDATE', $table, 'SET', implode(',', $sql)];

        if (is_array($conditions)) {
            $where = $this->where($conditions, $parameters, $paramIndex, $paramPrefix);
            if ($where) {
                $sql[] = 'WHERE';
                $sql[] = implode(' AND ', $where);
            }
        } elseif ($conditions = trim($conditions ?? '')) {
            $sql[] = "WHERE $conditions";
        }

        $sql = implode(' ', $sql);

        return $this->execute($sql, $parameters) ?? 0;
    }

    /**
     * @throws DbException
     */
    public function delete(string $table, string|array $conditions = null): int
    {
        $sql = ['DELETE FROM', $table];

        $parameters = [];
        if (is_array($conditions)) {
            $where = $this->where($conditions, $parameters);
            if ($where) {
                $sql[] = 'WHERE';
                $sql[] = implode(' AND ', $where);
            }
        } elseif ($conditions = trim($conditions)) {
            $sql[] = "WHERE $conditions";
        }

        $sql = implode(' ', $sql);

        return $this->execute($sql, $parameters) ?? 0;
    }

    /**
     * @throws \Throwable
     */
    public function transactional(\Closure $callback, \Closure $fallback = null, \Closure $complete = null): mixed
    {
        try {
            $this->beginTransaction();

            $result = call_user_func($callback, $this);
            $this->commit();
        } catch (\Throwable $result) {
            $this->rollback();

            if ($fallback) {
                $result = call_user_func($fallback) ?: $result;
            }
        } finally {
            if ($complete) {
                call_user_func($complete);
            }
        }

        if (isset($result)) {
            if ($result instanceof \Throwable) {
                throw $result;
            }
        }

        return $result;
    }

    public function beginTransaction(): void
    {
        $this->getDriver()->beginTransaction();
    }

    public function commit(): void
    {
        $this->getDriver()->commit();
    }

    public function rollback(): void
    {
        $this->getDriver()->rollback();
    }

    public function database(): string
    {
        return $this->getDriver()->getCurrentDatabase();
    }

    /**
     * Checks if a specified table exists in the database and returns the number of records in the table.
     *
     * If the specified table exists, but contains no records, it returns 0.
     * If the specified table does not exist, it returns NULL.
     *
     * @param string $table The name of the table to check.
     * @param string|null $database The database of the table.
     * @return int|null The number of records in the table if it exists and contains records,
     *                 0 if the table exists but contains no records,
     *                 NULL if the table does not exist.
     */
    public function exists(string $table, string $database = null): ?int
    {
        if ($database ??= $this->getDatabase() ?: $this->database()) {
            $sql = "SELECT 
       CASE 
                WHEN EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = :database AND table_name = :table) THEN
                    (SELECT COUNT(*) FROM $table)
                ELSE
                    NULL
        END AS record_count;";

            return $this->query($sql, compact('database', 'table'))
                ->fetchColumn(0);
        }

        return null;
    }
}