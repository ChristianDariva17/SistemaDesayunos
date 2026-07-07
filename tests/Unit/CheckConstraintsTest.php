<?php

declare(strict_types=1);

use App\Support\Database\CheckConstraints;
use Illuminate\Support\Facades\DB;

it('builds check constraint SQL for supported drivers', function (): void {
    expect(CheckConstraints::addSql('orders', 'orders_total_check', 'total >= 0', 'mysql'))
        ->toBe('ALTER TABLE orders ADD CONSTRAINT orders_total_check CHECK (total >= 0)')
        ->and(CheckConstraints::dropSql('orders', 'orders_total_check', 'mysql'))
        ->toBe('ALTER TABLE orders DROP CHECK orders_total_check')
        ->and(CheckConstraints::dropSql('orders', 'orders_total_check', 'mariadb'))
        ->toBe('ALTER TABLE orders DROP CONSTRAINT orders_total_check')
        ->and(CheckConstraints::dropSql('orders', 'orders_total_check', 'pgsql'))
        ->toBe('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_total_check')
        ->and(CheckConstraints::dropSql('orders', 'orders_total_check', 'sqlsrv'))
        ->toBe('ALTER TABLE orders DROP CONSTRAINT orders_total_check');
});

it('builds MariaDB drop SQL when a mysql connection reports a MariaDB server version', function (): void {
    expect(CheckConstraints::dropSql('orders', 'orders_total_check', 'mysql', '10.4.32-MariaDB'))
        ->toBe('ALTER TABLE orders DROP CONSTRAINT orders_total_check');
});

it('builds MySQL drop SQL when a mysql connection reports a true MySQL server version', function (): void {
    expect(CheckConstraints::dropSql('orders', 'orders_total_check', 'mysql', '8.0.36'))
        ->toBe('ALTER TABLE orders DROP CHECK orders_total_check');
});

it('detects MariaDB through the default Laravel mysql connection path', function (): void {
    DB::shouldReceive('getDriverName')
        ->once()
        ->andReturn('mysql');

    DB::shouldReceive('connection->getPdo->getAttribute')
        ->once()
        ->with(\PDO::ATTR_SERVER_VERSION)
        ->andReturn('10.4.32-MariaDB');

    expect(CheckConstraints::dropSql('orders', 'orders_total_check'))
        ->toBe('ALTER TABLE orders DROP CONSTRAINT orders_total_check');
});

it('reports unsupported check constraint drivers with an actionable message', function (): void {
    CheckConstraints::dropSql('orders', 'orders_total_check', 'sqlite');
})->throws(RuntimeException::class, 'Unsupported database driver "sqlite" for check constraints. Supported drivers: mysql, mariadb, pgsql, sqlsrv.');
