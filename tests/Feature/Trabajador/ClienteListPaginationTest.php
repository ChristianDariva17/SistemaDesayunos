<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

it('bounds worker client page sizes', function (mixed $requested, int $expected): void {
    $worker = User::factory()->create([
        'rol' => 'trabajador',
    ]);
    $parameters = $requested === null ? [] : ['per_page' => $requested];

    $response = $this->actingAs($worker)->get(route('trabajador.clientes.index', $parameters));

    $response->assertOk()->assertViewHas('clientes');

    $clientes = $response->viewData('clientes');

    expect($clientes)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($clientes->perPage())->toBe($expected);
})->with([
    'missing' => [null, 10],
    'ten' => [10, 10],
    'twenty five' => [25, 25],
    'fifty' => [50, 50],
    'one hundred' => [100, 100],
    'nonnumeric' => ['many', 10],
    'zero' => [0, 10],
    'negative' => [-10, 10],
    'unsupported' => [75, 10],
    'oversized' => [1000, 10],
]);
