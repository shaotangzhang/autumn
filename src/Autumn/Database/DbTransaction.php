<?php

namespace Autumn\Database;

use Autumn\Exceptions\SystemException;
use Throwable;


//// 假设有两个数据库连接 $pdo1 和 $pdo2
//
//try {
//    // 开始全局事务
//    $pdo1->beginTransaction();    DONE
//    $pdo2->beginTransaction();    DONE
//
//    // 执行跨连接的操作
//    $pdo1->exec("INSERT INTO table1 ...");    DONE
//    $pdo2->exec("INSERT INTO table2 ...");    DONE
//
//    // 准备阶段
//    $pdo1->exec("XA START 'transaction_id'");    DONE
//    $pdo2->exec("XA START 'transaction_id'");    DONE
//
//    // 执行具体操作
//    $pdo1->exec("INSERT INTO table1 ...");    DONE
//    $pdo2->exec("INSERT INTO table2 ...");    DONE
//
//    // 准备提交
//    $pdo1->exec("XA END 'transaction_id'");    DONE
//    $pdo1->exec("XA PREPARE 'transaction_id'");
//    $pdo2->exec("XA END 'transaction_id'");    DONE
//    $pdo2->exec("XA PREPARE 'transaction_id'");
//
//    // 提交阶段
//    $pdo1->exec("XA COMMIT 'transaction_id'");
//    $pdo2->exec("XA COMMIT 'transaction_id'");
//
//} catch (Exception $e) {
//    // 出现错误时回滚
//    $pdo1->exec("XA ROLLBACK 'transaction_id'");
//    $pdo2->exec("XA ROLLBACK 'transaction_id'");
//}


/**
 * Class DbTransaction
 *
 * This class manages transactions on a single database connection, including nested transactions using savepoints.
 */
class DbTransaction
{
    /**
     * @var DbConnection The database connection.
     */
    private DbConnection $connection;

    /**
     * @var array Holds the savepoints.
     */
    private array $savePoints = [];

    /**
     * @var int A counter used for generating unique savepoint names.
     */
    private int $counter = 0;

    /**
     * Constructor
     *
     * @param DbConnection $connection The database connection.
     */
    public function __construct(DbConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Begins a transaction, creating a savepoint if nested.
     */
    public function begin(): void
    {
        if ($this->counter === 0) {
            $this->connection->beginTransaction();
        } else {
            $savePointName = 'savePoint' . $this->counter;
            $this->connection->createSavePoint($savePointName);
            $this->savePoints[] = $savePointName;
        }
        $this->counter++;
    }

    /**
     * Commits the transaction, releasing the most recent savepoint if nested.
     */
    public function commit(): void
    {
        if ($this->counter === 1) {
            $this->connection->commit();
        } else {
            $savePointName = array_pop($this->savePoints);
            $this->connection->releaseSavePoint($savePointName);
        }
        $this->counter--;
    }

    /**
     * Rolls back the transaction to the most recent savepoint if nested.
     */
    public function rollback(): void
    {
        if ($this->counter === 1) {
            $this->connection->rollback();
        } else {
            $savePointName = array_pop($this->savePoints);
            $this->connection->rollbackToSavePoint($savePointName);
        }
        $this->counter--;
    }

    /**
     * Submits all transactions by releasing their savepoints.
     */
    private function submit(): void
    {
        while ($this->counter > 1) {
            $this->commit();
        }
    }

    /**
     * Cancels all transactions by rolling back to their savepoints.
     */
    private function cancel(): void
    {
        while ($this->counter > 1) {
            $this->rollback();
        }
    }

    /**
     * Processes a callable function within a transaction context.
     * Submits the transaction if the function succeeds, otherwise cancels the transaction.
     *
     * @param callable $func The function to call.
     * @param array|null $args The arguments to pass to the function.
     * @return mixed The result of the function call.
     * @throws \Throwable If the function throws an exception.
     */
    public function process(callable $func, array $args = null): mixed
    {
        $this->begin();
        try {
            $result = call_user_func_array($func, $args ?? []);
            $this->commit();
            return $result;
        } catch (\Throwable $ex) {
            $this->rollback();
            throw $ex;
        } finally {
            $this->counter = 0;
            $this->savePoints = [];
        }
    }
}
