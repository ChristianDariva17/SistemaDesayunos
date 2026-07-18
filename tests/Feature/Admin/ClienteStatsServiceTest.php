<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\User;
use App\Services\ClienteStatsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    DB::table('clientes')->delete();
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('returns integer zeroes from one query when there are no clientes', function (): void {
    DB::enableQueryLog();
    DB::flushQueryLog();

    $summary = app(ClienteStatsService::class)->indexSummary();

    expect($summary)->toBe([
        'totalClientes' => 0,
        'clientesActivos' => 0,
        'clientesInactivos' => 0,
        'nuevosEsteMes' => 0,
    ])->and(DB::getQueryLog())->toHaveCount(1);

    DB::disableQueryLog();
});

it('counts active and inactive clientes without classifying malformed statuses', function (): void {
    Carbon::setTestNow('2026-07-17 12:00:00');

    foreach (['activo', 'inactivo', 'desconocido'] as $index => $estado) {
        Cliente::create([
            'nombre' => "Stats status {$index}",
            'estado' => $estado,
        ]);
    }

    expect(app(ClienteStatsService::class)->indexSummary())->toBe([
        'totalClientes' => 3,
        'clientesActivos' => 1,
        'clientesInactivos' => 1,
        'nuevosEsteMes' => 3,
    ]);
});

it('uses a half-open current-month timestamp range', function (): void {
    Carbon::setTestNow('2026-07-17 12:00:00');

    foreach ([
        '2026-06-30 23:59:59',
        '2026-07-01 00:00:00',
        '2026-07-31 23:59:59',
        '2026-08-01 00:00:00',
    ] as $index => $createdAt) {
        DB::table('clientes')->insert([
            'nombre' => "Stats boundary {$index}",
            'estado' => 'activo',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    expect(app(ClienteStatsService::class)->indexSummary()['nuevosEsteMes'])->toBe(2);
});

it('passes the summary to the admin clientes index view', function (): void {
    Carbon::setTestNow('2026-07-17 12:00:00');
    $admin = User::factory()->create(['rol' => 'administrador']);

    Cliente::create(['nombre' => 'Stats active', 'estado' => 'activo']);
    Cliente::create(['nombre' => 'Stats inactive', 'estado' => 'inactivo']);

    $response = $this->actingAs($admin)->get(route('admin.clientes.index'));

    $response->assertOk()
        ->assertViewHas('totalClientes', 2)
        ->assertViewHas('clientesActivos', 1)
        ->assertViewHas('clientesInactivos', 1)
        ->assertViewHas('nuevosEsteMes', 2)
        ->assertViewHas('clientes');
});

it('keeps the admin clientes index unavailable to workers', function (): void {
    $worker = User::factory()->create(['rol' => 'trabajador']);

    $this->actingAs($worker)
        ->get(route('admin.clientes.index'))
        ->assertForbidden();
});
