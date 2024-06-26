<?php
/**
 * Autumn PHP Framework
 *
 * Date:        26/06/2024
 */

namespace Autumn\Database;

use Autumn\Exceptions\SystemException;
use PHPUnit\Framework\TestCase;

class DbTransactionTest extends TestCase
{
    private $dbConnectionMock;

    protected function setUp(): void
    {
        $this->dbConnectionMock = $this->createMock(DbConnection::class);
    }

    public function testBeginTransaction()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);

        $this->dbConnectionMock->expects($this->once())
            ->method('beginTransaction');

        $transaction->begin();
    }

    public function testCommitTransaction()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);

        $this->dbConnectionMock->expects($this->once())
            ->method('commit');

        $transaction->begin();
        $transaction->commit();
    }

    public function testRollbackTransaction()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);

        $this->dbConnectionMock->expects($this->once())
            ->method('rollback');

        $transaction->begin();
        $transaction->rollback();
    }

    public function testCreateSavePoint()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);

        $this->dbConnectionMock->expects($this->once())
            ->method('beginTransaction');

        $this->dbConnectionMock->expects($this->once())
            ->method('createSavePoint')
            ->with('savePoint1');

        $transaction->begin();
        $transaction->begin();
    }

    public function testReleaseSavePoint()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);

        $this->dbConnectionMock->expects($this->once())
            ->method('beginTransaction');

        $this->dbConnectionMock->expects($this->once())
            ->method('createSavePoint')
            ->with('savePoint1');

        $this->dbConnectionMock->expects($this->once())
            ->method('releaseSavePoint')
            ->with('savePoint1');

        $transaction->begin();
        $transaction->begin();
        $transaction->commit();
    }

    public function testRollbackToSavePoint()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);

        $this->dbConnectionMock->expects($this->once())
            ->method('beginTransaction');

        $this->dbConnectionMock->expects($this->once())
            ->method('createSavePoint')
            ->with('savePoint1');

        $this->dbConnectionMock->expects($this->once())
            ->method('rollbackToSavePoint')
            ->with('savePoint1');

        $transaction->begin();
        $transaction->begin();
        $transaction->rollback();
    }

//    public function testSubmit()
//    {
//        $transaction = new DbTransaction($this->dbConnectionMock);
//
//        $this->dbConnectionMock->expects($this->once())
//            ->method('beginTransaction');
//
//        $this->dbConnectionMock->expects($this->once())
//            ->method('createSavePoint')
//            ->with('savePoint1');
//
//        $this->dbConnectionMock->expects($this->once())
//            ->method('releaseSavePoint')
//            ->with('savePoint1');
//
//        $transaction->begin();
//        $transaction->begin();
//        $transaction->submit();
//    }
//
//    public function testCancel()
//    {
//        $transaction = new DbTransaction($this->dbConnectionMock);
//
//        $this->dbConnectionMock->expects($this->once())
//            ->method('beginTransaction');
//
//        $this->dbConnectionMock->expects($this->once())
//            ->method('createSavePoint')
//            ->with('savePoint1');
//
//        $this->dbConnectionMock->expects($this->once())
//            ->method('rollbackToSavePoint')
//            ->with('savePoint1');
//
//        $transaction->begin();
//        $transaction->begin();
//        $transaction->cancel();
//    }

    public function testProcessSuccessful()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);

        $this->dbConnectionMock->expects($this->once())
            ->method('beginTransaction');

        $this->dbConnectionMock->expects($this->once())
            ->method('commit');

        $result = $transaction->process(function () {
            return 'success';
        });

        $this->assertEquals('success', $result);
    }

    public function testProcessFailure()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);

        $this->dbConnectionMock->expects($this->once())
            ->method('beginTransaction');

        $this->dbConnectionMock->expects($this->once())
            ->method('rollback');

        $this->expectException(\Exception::class);

        $transaction->process(function () {
            throw new \Exception('failure');
        });
    }
}
