<?php

declare(strict_types=1);

namespace App\Http\Requests\Pedido;

use App\Enums\PaymentMethod;
use App\Enums\ProductoEstado;
use App\Models\Pedido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePedidoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->has('observaciones') && is_string($this->input('observaciones'))) {
            $observaciones = trim($this->input('observaciones'));
            $normalized['observaciones'] = $observaciones === '' ? null : $observaciones;
        }

        if ($this->has('productos') && is_array($this->input('productos'))) {
            $normalized['productos'] = array_map(static function (mixed $producto): mixed {
                if (! is_array($producto)) {
                    return $producto;
                }

                if (array_key_exists('producto_id', $producto) && ! array_key_exists('id', $producto)) {
                    $producto['id'] = $producto['producto_id'];
                }

                return $producto;
            }, $this->input('productos'));
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('create', Pedido::class) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => ['required', Rule::exists('clientes', 'id')->where('estado', 'activo')],
            'empleado_id' => ['required', Rule::exists('empleados', 'id')->where('estado', 'activo')],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.id' => ['required', 'distinct', Rule::exists('productos', 'id')->where('estado', ProductoEstado::Active->value)],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],
            'metodo_pago' => ['nullable', Rule::enum(PaymentMethod::class)],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debes seleccionar un cliente',
            'productos.required' => 'Debes agregar al menos un producto',
            'productos.min' => 'Debes agregar al menos un producto',
            'productos.*.id.distinct' => 'No puedes agregar el mismo producto más de una vez',
        ];
    }
}
