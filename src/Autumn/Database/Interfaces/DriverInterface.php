<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Interfaces;

use Autumn\Database\DbException;

interface DriverInterface
{
    public function exec(string $sql, array $parameters): mixed;

    public function insert(string $sql, array $parameters): string|int;

    public function fetch(mixed $result, int $mode = null): ?array;

    public function exists(mixed $result): bool;

    public function getInsertedId(mixed $result = null): int;

    public function getAffectedRows(mixed $result = null): false|int;

    public function getCurrentDatabase(): ?string;

    public function getException(): ?DbException;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;

    public function startCrossTransaction(string $xid): bool;

    public function endCrossTransaction(string $xid): bool;

    public function createSavePoint(string $savePoint): bool;

    public function releaseSavePoint(string $savePoint): bool;

    public function rollbackToSavePoint(string $savePoint): bool;
}