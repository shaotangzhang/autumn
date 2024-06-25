<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Drivers\Pdo;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Db;
use Autumn\Database\DbConnection;
use Autumn\Database\DbException;
use Autumn\Database\Interfaces\DriverInterface;
use Autumn\Exceptions\SystemException;

class Driver implements DriverInterface
{

    public const DEFAULT_TYPE = 'mysql';
    public const DEFAULT_HOST = 'localhost';
    public const DEFAULT_PORT = 3306;
    public const DEFAULT_USER = 'root';
    public const DEFAULT_PASS = null;
    public const DEFAULT_DATABASE = null;
    public const DEFAULT_PREFIX = null;
    public const DEFAULT_CHARSET = 'utf8mb4';
    public const DEFAULT_COLLATION = 'utf8mb4_general_ci';
    public const DEFAULT_ENGINE = 'InnoDB';
    public const DEFAULT_SOCKET = null;

    private int $error = 0;
    private string $message = '';

    private ?\PDO $pdo = null;
    private mixed $lastInsertedId = null;

    public function __construct(private DbConnection $connection)
    {

    }

    private function connect(): void
    {
        if (!$this->pdo) {
            $config = $this->connection;

            $dsn = $config->getType() ?: static::DEFAULT_TYPE;
            $dsn .= ':host=' . $config->getHost();
            $dsn .= ';port=' . $config->getPort();

            if ($database = $config->getDatabase()) {
                $dsn .= ';dbname=' . $database;
            }

            if ($charset = $config->getCharset() ?: static::DEFAULT_CHARSET) {
                $dsn .= ';charset=' . str_replace('-', '', $charset);
            }

            try {
                $this->pdo = new \PDO($dsn, $config->getUser(), $config->getPass(), $config->getOptions());
                $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $ex) {
                $this->error = $ex->getCode();
                $this->message = $ex->getMessage();
            }
        }
    }

    private function prepare(string &$sql, array &$args = null): ?\PDOStatement
    {
        if (empty($args)) {
            return $this->pdo->prepare($sql);
        }

        $n = 0;

        $keys = array_flip(array_keys($args));

        $sql = preg_replace_callback('/:([a-zA-Z]\w*)/', function (array $matches) use (&$n, &$args, &$keys) {
            $key = $matches[1];
            $value = $args[$key] ?? null;

            if ($value === null) {
                return 'null';
            }

            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            if (is_array($value)) {
                $temp = [];
                foreach ($value as $val) {

                    if ($val === null) {
                        $temp[] = 'null';
                        continue;
                    }

                    if (is_bool($val)) {
                        $temp[] = $val ? 'true' : 'false';
                        continue;
                    }

                    if (is_array($val)) {
                        $val = json_encode($val);
                    } elseif ($val instanceof \DateTimeInterface) {
                        $val = Db::formatDateTime($val);
                    }

                    if (!in_array($val, $temp, true)) {
                        do {
                            $idx = Db::PARAMETER_PREFIX . $n++;
                        } while (array_key_exists($idx, $args));

                        $temp[] = ":$idx";
                        $args[$idx] = $val;
                    }
                }

                if (empty($temp)) {
                    throw new \InvalidArgumentException('The parameter is bond with an empty array.');
                }

                return '(' . implode(', ', $temp) . ')';
            } else {

                if ($value instanceof \DateTimeInterface) {
                    $value = Db::formatDateTime($value);
                }

                unset($keys[$key]);
                $args[$key] = $value;
                return $matches[0];
            }
        }, $sql);

        foreach ($keys as $key => $any) {
            unset($args[$key]);
        }

        return $this->pdo->prepare($sql);
    }

    public function exec(string $sql, array $parameters): mixed
    {
        $this->error = 0;
        $this->message = '';

        $this->connect();

        if ($statement = $this->prepare($sql, $parameters)) {
            try {
                $this->lastInsertedId = $this->pdo->lastInsertId();

                Db::addHistory($sql, $parameters);
                if ($statement->execute($parameters)) {
                    return $statement;
                }

                [, $this->error, $this->message] = $this->pdo->errorInfo();
            } catch (\PDOException $ex) {
                $this->error = 500; //$ex->getCode();
                $this->message = $ex->getMessage();
            }
        }

        return null;
    }

    /**
     * @throws DbException
     */
    public function insert(string $sql, array $parameters): string|int
    {
        if ($this->exec($sql, $parameters)) {
            $id = $this->pdo->lastInsertId();
            if ($id !== $this->lastInsertedId) {
                return $id;
            }

            return 0;
        }

        throw $this->getException();
    }

    public function exists(mixed $result): bool
    {
        if (!$result instanceof \PDOStatement) {
            return false;
        }

        return $result->fetchColumn() !== false;
    }

    public function fetch(mixed $result, int $mode = null): ?array
    {
        if (!$result instanceof \PDOStatement) {
            return null;
        }

        switch ($mode) {
            case Db::FETCH_BOTH:
                return $result->fetch(\PDO::FETCH_BOTH) ?: null;

            case Db::FETCH_NUM:
                return $result->fetch(\PDO::FETCH_NUM) ?: null;

            case Db::FETCH_META:
                $columns = [];
                $index = 0;
                while ($column = $result->getColumnMeta($index)) {
                    $flags = $column['flags'] ?? [];

                    $columns[$index] = Column::from([
                        'table' => $column['table'],
                        'name' => $column['name'],
                        'size' => $column['len'],
                        'precision' => $column['precision'],
                        'nullable' => !in_array('not_null', $flags),
                        'primary_key' => in_array('primary_key', $flags),
                        'unique_key' => in_array('unique_key', $flags),
                        'multiple_key' => in_array('multiple_key', $flags)
                    ]);

                    $index++;
                }
                return $columns;

            case Db::FETCH_ASSOC:
            default:
                return $result->fetch(\PDO::FETCH_ASSOC) ?: null;
        }
    }

    public function getInsertedId(mixed $result = null): int
    {
        return $this->pdo?->lastInsertId();
    }

    public function getAffectedRows(mixed $result = null): false|int
    {
        if ($result instanceof \PDOStatement) {
            return $result->rowCount();
        }

        return false;
    }

    public function getCurrentDatabase(): ?string
    {
        $sql = 'SELECT database()';
        if ($statement = $this->exec($sql, [])) {
            $rs = $this->fetch($statement, Db::FETCH_NUM);
            return reset($rs);
        }

        return null;
    }

    public function getException(): ?DbException
    {
        if ($this->error) {
            return new DbException($this->message, $this->error);
        }

        return null;
    }
    /**
     * Begins a standard transaction.
     */
    public function beginTransaction(): void
    {
        $this->connect();
        $this->pdo?->beginTransaction();
    }

    /**
     * Commits the current transaction.
     */
    public function commit(): void
    {
        $this->pdo?->commit();
    }

    /**
     * Rolls back the current transaction.
     */
    public function rollback(): void
    {
        $this->pdo?->rollback();
    }

    /**
     * Starts an XA transaction with the given transaction ID (XID).
     *
     * @param string $xid The transaction ID.
     * @return bool True on success, false on failure.
     * @throws SystemException If preparing the XA START statement fails.
     */
    public function startCrossTransaction(string $xid): bool
    {
        $this->connect();
        return $this->prepareAndExecute('XA START :xid', ['xid' => $xid]);
    }

    /**
     * Ends the XA transaction with the given transaction ID (XID).
     *
     * @param string $xid The transaction ID.
     * @return bool True on success, false on failure.
     * @throws SystemException If preparing the XA END statement fails.
     */
    public function endCrossTransaction(string $xid): bool
    {
        return $this->prepareAndExecute('XA END :xid', ['xid' => $xid]);
    }

    /**
     * Creates a savepoint with the given name.
     *
     * @param string $savePoint The name of the savepoint.
     * @return bool True on success, false on failure.
     * @throws SystemException If preparing the SAVEPOINT statement fails.
     */
    public function createSavePoint(string $savePoint): bool
    {
        $this->connect();
        return $this->prepareAndExecute('SAVEPOINT :name', ['name' => $savePoint]);
    }

    /**
     * Releases the savepoint with the given name.
     *
     * @param string $savePoint The name of the savepoint.
     * @return bool True on success, false on failure.
     * @throws SystemException If preparing the RELEASE SAVEPOINT statement fails.
     */
    public function releaseSavePoint(string $savePoint): bool
    {
        return $this->prepareAndExecute('RELEASE SAVEPOINT :name', ['name' => $savePoint]);
    }

    /**
     * Rolls back the transaction to the savepoint with the given name.
     *
     * @param string $savePoint The name of the savepoint.
     * @return bool True on success, false on failure.
     * @throws SystemException If preparing the ROLLBACK TO SAVEPOINT statement fails.
     */
    public function rollbackToSavePoint(string $savePoint): bool
    {
        return $this->prepareAndExecute('ROLLBACK TO SAVEPOINT :name', ['name' => $savePoint]);
    }

    /**
     * Prepares and executes an SQL statement with the given parameters.
     *
     * @param string $sql The SQL statement to prepare and execute.
     * @param array|null $params The parameters to bind to the SQL statement.
     * @return bool True on success, false on failure.
     * @throws SystemException If preparing the SQL statement fails.
     */
    private function prepareAndExecute(string $sql, array $params = null): bool
    {
        if ($this->pdo) {
            $statement = $this->pdo->prepare($sql);
            if ($statement === false) {
                throw new SystemException(sprintf('Failed to prepare statement: %s', $sql));
            }
            return $statement->execute($params);
        }
        return false;
    }
}