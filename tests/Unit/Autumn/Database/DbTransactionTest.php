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
    private $dbConnectionMockOther;

    protected function setUp(): void
    {
        $this->dbConnectionMock = $this->createMock(DbConnection::class);
        $this->dbConnectionMockOther = $this->createMock(DbConnection::class);
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

    public function testStartCrossTransaction()
    {
        $this->expectException(SystemException::class);

        $transaction = new DbTransaction($this->dbConnectionMock);
        $xid = 'XID0';

        $this->dbConnectionMock->expects($this->once())
            ->method('startCrossTransaction')
            ->with($xid)
            ->willReturn(true);

        $transaction->begin($this->dbConnectionMock);
        $transaction->begin($this->dbConnectionMockOther);
    }

    public function testCreateSavePoint()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);
        $savePointName = 'savePoint1';

        $this->dbConnectionMock->expects($this->once())
            ->method('createSavePoint')
            ->with($savePointName)
            ->willReturn(true);

        $transaction->begin($this->dbConnectionMock);
        $transaction->begin($this->dbConnectionMock);
        // $transaction->createSavePoint($this->dbConnectionMock, $savePointName);
    }

    public function testReleaseSavePoint()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);
        $savePointName = 'savePoint1';

        $this->dbConnectionMock->expects($this->once())
            ->method('releaseSavePoint')
            ->with($savePointName)
            ->willReturn(true);

        $transaction->begin($this->dbConnectionMock);
        $transaction->begin($this->dbConnectionMock);
        // $transaction->createSavePoint($this->dbConnectionMock, $savePointName);
        $this->assertTrue($transaction->commit($this->dbConnectionMock));
    }

    public function testRollbackToSavePoint()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);
        $savePointName = 'savePoint1';

        $this->dbConnectionMock->expects($this->once())
            ->method('rollbackToSavePoint')
            ->with($savePointName)
            ->willReturn(true);

        $transaction->begin($this->dbConnectionMock);
        $transaction->begin($this->dbConnectionMock);
        // $transaction->createSavePoint($this->dbConnectionMock, $savePointName);
        $this->assertTrue($transaction->rollback($this->dbConnectionMock));
    }

    public function testSubmit()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);
        $savePointName = 'savePoint1';

        $this->dbConnectionMock->expects($this->once())
            ->method('createSavePoint')
            ->with($savePointName)
            ->willReturn(true);

        $this->dbConnectionMock->expects($this->once())
            ->method('releaseSavePoint')
            ->with($savePointName)
            ->willReturn(true);

        $transaction->begin($this->dbConnectionMock);
        $transaction->begin($this->dbConnectionMock);
        // $transaction->createSavePoint($this->dbConnectionMock, $savePointName);
        $transaction->commit();
    }

    public function testCancel()
    {
        $transaction = new DbTransaction($this->dbConnectionMock);
        $savePointName = 'savePoint1';

        $this->dbConnectionMock->expects($this->once())
            ->method('createSavePoint')
            ->with($savePointName)
            ->willReturn(true);

        $this->dbConnectionMock->expects($this->once())
            ->method('rollbackToSavePoint')
            ->with($savePointName)
            ->willReturn(true);

        $transaction->begin($this->dbConnectionMock);
        $transaction->begin($this->dbConnectionMock);
        //$transaction->createSavePoint($this->dbConnectionMock, $savePointName);
        $transaction->rollback();
    }

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
