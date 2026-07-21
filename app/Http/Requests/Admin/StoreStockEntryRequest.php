<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Producto;
use App\Support\InventoryLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreStockEntryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('motivo')) {
            return;
        }

        $value = $this->input('motivo');

        if (! is_string($value)) {
            return;
        }

        $value = $this->trimUnicodeWhitespace($value);

        $this->merge([
            'motivo' => $value === '' ? null : $value,
        ]);
    }

    private function trimUnicodeWhitespace(string $value): string
    {
        return preg_replace('/^[\s\p{Z}\x{FEFF}]+|[\s\p{Z}\x{FEFF}]+$/u', '', $value) ?? trim($value);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('updateStock', Producto::class) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:'.InventoryLimits::MAX_STOCK_LEVEL],
            'motivo' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('producto_id') || $validator->errors()->has('cantidad')) {
                    return;
                }

                $producto = Producto::query()->find((int) $this->input('producto_id'));

                if (! $producto instanceof Producto) {
                    return;
                }

                $cantidad = (int) $this->input('cantidad');
                $stockActual = (int) $producto->stock;

                if ($stockActual + $cantidad > InventoryLimits::MAX_STOCK_LEVEL) {
                    $validator->errors()->add('cantidad', 'La cantidad supera el stock máximo permitido para este producto.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'producto_id.required' => 'Debes seleccionar un producto.',
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'cantidad.max' => 'La cantidad supera el máximo permitido para movimientos de stock.',
            'motivo.max' => 'El motivo no puede tener más de 255 caracteres.',
        ];
    }
}
