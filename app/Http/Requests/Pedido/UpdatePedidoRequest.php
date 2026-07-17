<?php

declare(strict_types=1);

namespace App\Http\Requests\Pedido;

use App\Enums\PedidoStatus;
use App\Models\Pedido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePedidoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('observaciones') || ! is_string($this->input('observaciones'))) {
            return;
        }

        $observaciones = trim($this->input('observaciones'));

        $this->merge([
            'observaciones' => $observaciones === '' ? null : $observaciones,
        ]);
    }

    public function authorize(): bool
    {
        $pedido = $this->route('pedido');

        return $pedido instanceof Pedido
            && ($this->user()?->can('update', $pedido) ?? false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::enum(PedidoStatus::class)],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }
}
