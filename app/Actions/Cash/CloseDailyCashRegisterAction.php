<?php

declare(strict_types=1);

namespace App\Actions\Cash;

use App\Models\DailyCashClosure;
use App\Models\Pedido;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class CloseDailyCashRegisterAction
{
    public function handle(CarbonInterface|string $businessDate, ?int $userId = null): DailyCashClosure
    {
        $normalizedDate = $this->normalizeBusinessDate($businessDate);
        $actorId = $userId ?? Auth::id();

        try {
            return DB::transaction(function () use ($normalizedDate, $actorId): DailyCashClosure {
                if (DailyCashClosure::query()->forBusinessDate($normalizedDate)->exists()) {
                    throw $this->duplicateClosureException($normalizedDate);
                }

                $pedidos = Pedido::query()
                    ->whereDate('fecha', $normalizedDate)
                    ->get(['id', 'estado', 'metodo_pago', 'total']);

                $settledPedidos = $pedidos->where('estado', 'completado');

                return DailyCashClosure::create([
                    'business_date' => $normalizedDate,
                    'total_orders' => $pedidos->count(),
                    'total_revenue' => $this->sumTotals($settledPedidos),
                    'settled_order_count' => $settledPedidos->count(),
                    'pending_order_count' => $pedidos->whereIn('estado', ['pendiente', 'procesando'])->count(),
                    'cancelled_order_count' => $pedidos->where('estado', 'cancelado')->count(),
                    'payment_method_totals' => $this->paymentMethodTotals($settledPedidos),
                    'closed_by_user_id' => $actorId,
                    'closed_at' => now(),
                ]);
            });
        } catch (QueryException $exception) {
            if ($this->isUniqueConstraintViolation($exception)) {
                throw $this->duplicateClosureException($normalizedDate);
            }

            throw $exception;
        }
    }

    private function normalizeBusinessDate(CarbonInterface|string $businessDate): string
    {
        if ($businessDate instanceof CarbonInterface) {
            return $businessDate->toDateString();
        }

        return CarbonImmutable::parse($businessDate)->toDateString();
    }

    private function duplicateClosureException(string $businessDate): DomainException
    {
        return new DomainException("Daily cash closure already exists for {$businessDate}.");
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);

        return $sqlState === '23505'
            || ($sqlState === '23000' && in_array($driverCode, [19, 1062, 1555, 2067], true));
    }

    /**
     * @param  Collection<int, Pedido>  $pedidos
     */
    private function sumTotals(Collection $pedidos): float
    {
        return round((float) $pedidos->sum(static fn (Pedido $pedido): float => (float) $pedido->total), 2);
    }

    /**
     * @param  Collection<int, Pedido>  $pedidos
     * @return array<string, array{count:int,total:float}>
     */
    private function paymentMethodTotals(Collection $pedidos): array
    {
        return $pedidos
            ->groupBy(static fn (Pedido $pedido): string => filled($pedido->metodo_pago) ? (string) $pedido->metodo_pago : 'unspecified')
            ->map(fn (Collection $group): array => [
                'count' => $group->count(),
                'total' => $this->sumTotals($group),
            ])
            ->sortKeys()
            ->all();
    }
}
