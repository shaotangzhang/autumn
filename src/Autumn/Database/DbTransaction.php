<?php

namespace Autumn\Database;

use Autumn\Exceptions\SystemException;
use Throwable;

/**
 * Class DbTransaction
 *
 * This class manages transactions across multiple database connections, including XA transactions and savepoints.
 */
class DbTransaction
{
    /**
     * @var DbConnection[] $connections Holds the database connections involved in the transaction.
     */
    private array $connections = [];

    /**
     * @var array $innerSavePoints Holds the save points for each connection.
     */
    private array $innerSavePoints = [];

    /**
     * @var int $counter A counter used for generating unique XID and savepoint names.
     */
    private int $counter = 0;

    /**
     * @var DbConnection|null A connection as default
     */
    private ?DbConnection $defaultConnection;

    /**
     * Constructor
     *
     * @param DbConnection|null $connection
     */
    public function __construct(DbConnection $connection = null)
    {
        $this->defaultConnection = $connection;
    }

    /**
     * Begins a transaction on the given connection.
     * For the first connection, it simply starts a transaction.
     * For additional connections, it starts an XA transaction.
     * If the connection is already part of the transaction, it creates a savepoint.
     *
     * @param DbConnection|null $connection The database connection.
     */
    public function begin(DbConnection $connection = null): void
    {
        if (!($connection ??= $this->defaultConnection)) {
            return;
        }

        $xid = array_search($connection, $this->connections, true);
        if ($xid === false) {
            $xid = 'XID' . $this->counter++;
            $this->connections[$xid] = $connection;
            $connection->beginTransaction();
            $this->startTransaction($connection, $xid);
        } else {
            $this->createSavePoint($connection, $xid);
        }
    }

    /**
     * Commits the transaction on the given connection, releasing the most recent savepoint if present.
     *
     * @param DbConnection|null $connection The database connection.
     * @return bool True if the savepoint was released, false otherwise.
     */
    public function commit(DbConnection $connection = null): bool
    {
        if (!($connection ??= $this->defaultConnection)) {
            return false;
        }

        if ($this->handleSavePoint($connection, 'releaseSavePoint') !== false) {
            if (in_array($connection, $this->connections)) {
                $connection->commit();
                return true;
            }
        }

        return false;
    }

    /**
     * Rolls back the transaction on the given connection to the most recent savepoint if present.
     *
     * @param DbConnection|null $connection The database connection.
     * @return bool True if the savepoint was rolled back, false otherwise.
     */
    public function rollback(DbConnection $connection = null): bool
    {
        if (!($connection ??= $this->defaultConnection)) {
            return false;
        }

        if ($this->handleSavePoint($connection, 'rollbackToSavePoint') !== false) {
            if (in_array($connection, $this->connections)) {
                $connection->rollback();
                return true;
            }
        }

        return true;
    }

    /**
     * Handles save points for commit and rollback operations.
     *
     * @param DbConnection $connection The database connection.
     * @param string $action The action to perform ('release' or 'forgive').
     * @return bool|null True if the savepoint was handled, false otherwise.
     */
    private function handleSavePoint(DbConnection $connection, string $action): ?bool
    {
        if ($xid = array_search($connection, $this->connections, true)) {
            return $this->$action($xid);
        }
        return null;
    }

    /**
     * Starts a transaction or XA transaction on the given connection.
     *
     * @param DbConnection $connection The database connection.
     * @param string $xid The transaction ID.
     */
    private function startTransaction(DbConnection $connection, string $xid): void
    {
        switch (count($this->connections)) {
            case 1:
                break;
            case 2:
                foreach ($this->connections as $xid => $db) {
                    if (!$db->startCrossTransaction($xid)) {
                        throw new SystemException('Unable to start a transaction on connection `%s`.', $db->getName());
                    }
                }
                break;
            default:
                if (!$connection->startCrossTransaction($xid)) {
                    throw new SystemException('Unable to start a transaction on connection `%s`.', $connection->getName());
                }
        }
    }

    /**
     * Creates a savepoint on the given connection.
     *
     * @param DbConnection $connection The database connection.
     * @param string $xid The transaction ID.
     */
    private function createSavePoint(DbConnection $connection, string $xid): void
    {
        $savePointName = 'savePoint' . $this->counter++;
        $this->innerSavePoints[$xid][] = $savePointName;
        $connection->createSavePoint($savePointName);
    }

    /**
     * Releases the most recent savepoint on the given connection.
     *
     * @param string $xid The transaction ID.
     * @return bool|null True if the savepoint was released, false otherwise.
     */
    private function releaseSavePoint(string $xid): ?bool
    {
        return $this->manageSavePoint($xid, 'releaseSavePoint');
    }

    /**
     * Rolls back to the most recent savepoint on the given connection.
     *
     * @param string $xid The transaction ID.
     * @return bool|null True if the savepoint was rolled back, false otherwise.
     */
    private function rollbackToSavePoint(string $xid): ?bool
    {
        return $this->manageSavePoint($xid, 'rollbackToSavePoint');
    }

    /**
     * Manages savepoints by executing the specified action.
     *
     * @param string $xid The transaction ID.
     * @param string $action The action to perform ('RELEASE SAVEPOINT' or 'ROLLBACK TO SAVEPOINT').
     * @return bool|null True if the action was successful, false otherwise.
     */
    private function manageSavePoint(string $xid, string $action): ?bool
    {
        if ($connection = $this->connections[$xid] ?? null) {
            if ($savePoints = $this->innerSavePoints[$xid] ?? null) {
                if ($savePoint = array_pop($savePoints)) {
                    return $connection->$action($savePoint);
                }
            }
        }

        return null;
    }

    /**
     * Submits all transactions by releasing their save points.
     */
    private function submit(): void
    {
        foreach ($this->connections as $xid => $connection) {
            if ($this->releaseSavePoint($xid) !== false) {
                $connection->commit();
            }
        }
    }

    /**
     * Cancels all transactions by rolling back to their save points.
     */
    private function cancel(): void
    {
        foreach ($this->connections as $xid => $connection) {
            if ($this->rollbackToSavePoint($xid) !== false) {
                $connection->rollback();
            }
        }
    }

    /**
     * Processes a callable function within a transaction context.
     * Submits the transaction if the function succeeds, otherwise cancels the transaction.
     *
     * @param callable $func The function to call.
     * @return mixed The result of the function call.
     * @throws Throwable If the function throws an exception.
     */
    public function process(callable $func): mixed
    {
        try {
            $this->begin();
            $result = call_user_func($func, $this);
            $this->submit();
            return $result;
        } catch (Throwable $ex) {
            $this->cancel();
            throw $ex;
        } finally {
            $this->finalize();
        }
    }

    /**
     * Finalizes the transaction by ending all XA transactions and clearing internal state.
     */
    private function finalize(): void
    {
        if (count($this->connections) > 1) {
            foreach ($this->connections as $xid => $connection) {
                $connection->endCrossTransaction($xid);
            }
        }
        $this->connections = [];
        $this->innerSavePoints = [];
        $this->counter = 0;
    }
}
