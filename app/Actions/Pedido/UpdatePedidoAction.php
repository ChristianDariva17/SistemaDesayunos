<?php

declare(strict_types=1);

namespace App\Actions\Pedido;

use App\Events\OrderCompleted;
use App\Models\Pedido;
use App\Models\User;
use App\Support\BusinessOperationLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

final class UpdatePedidoAction
{
    /**
     * @var array<string, array<int, string>>
     */
    private const ALLOWED_STATUS_TRANSITIONS = [
        'pendiente' => ['procesando', 'cancelado'],
        'procesando' => ['completado', 'cancelado'],
        'completado' => [],
        'cancelado' => ['pendiente'],
    ];

    public function __construct(
        private readonly CancelPedidoAction $cancelPedido,
        private readonly ReactivatePedidoAction $reactivatePedido,
    ) {}

    /**
     * @param  array{estado:string,observaciones?:string|null}  $data
     */
    public function handle(array $data, Pedido $pedido, ?int $userId = null): Pedido
    {
        $estadoAnterior = null;
        $requestedPedidoId = (int) $pedido->getKey();

        try {
            $pedido = DB::transaction(function () use ($data, $pedido, $userId, &$estadoAnterior): Pedido {
                $pedido = Pedido::query()
                    ->with('productos')
                    ->lockForUpdate()
                    ->findOrFail($pedido->getKey());

                $estadoAnterior = $pedido->estado;
                $this->validateStatusTransition($estadoAnterior, $data['estado']);

                $user = $userId === null ? null : User::find($userId);

                if ($data['estado'] === 'cancelado' && $estadoAnterior !== 'cancelado') {
                    return $this->cancelPedido->handle(
                        $pedido,
                        $data['observaciones'] ?? null,
                        $user,
                    );
                }

                if ($estadoAnterior === 'cancelado' && $data['estado'] !== 'cancelado') {
                    return $this->reactivatePedido->handle(
                        $pedido,
                        $data['estado'],
                        $data['observaciones'] ?? null,
                        $user,
                    );
                }

                $pedido->update($data);

                return $pedido->refresh();
            });
        } catch (Throwable $exception) {
            BusinessOperationLogger::failure('pedido.update', $exception, [
                'model_id' => $requestedPedidoId,
                'user_id' => $userId,
                'business_date' => $pedido->fecha,
            ]);

            throw $exception;
        }

        Log::info('Pedido actualizado', [
            'pedido_id' => $pedido->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $pedido->estado,
            'usuario' => $userId ?? 'Sistema',
        ]);

        if ($estadoAnterior !== 'completado' && $pedido->estado === 'completado') {
            DB::afterCommit(static fn (): mixed => OrderCompleted::dispatch($pedido->id, $userId, (string) $pedido->fecha));
        }

        return $pedido;
    }

    /**
     * @throws ValidationException
     */
    private function validateStatusTransition(string $currentStatus, string $nextStatus): void
    {
        if ($currentStatus === $nextStatus) {
            return;
        }

        $allowedNextStatuses = self::ALLOWED_STATUS_TRANSITIONS[$currentStatus] ?? [];

        if (in_array($nextStatus, $allowedNextStatuses, true)) {
            return;
        }

        throw ValidationException::withMessages([
            'estado' => "The pedido status cannot transition from {$currentStatus} to {$nextStatus}.",
        ]);
    }
}
