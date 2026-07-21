<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Producto;
use App\Support\InventoryLimits;
use Illuminate\Foundation\Http\FormRequest;

final class StoreStockAdjustmentRequest extends FormRequest
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
            'stock_nuevo' => ['required', 'integer', 'min:0', 'max:'.InventoryLimits::MAX_STOCK_LEVEL],
            'motivo' => ['required', 'string', 'not_regex:/^\s*$/', 'max:255'],
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
            'stock_nuevo.required' => 'El nuevo stock es obligatorio.',
            'stock_nuevo.integer' => 'El nuevo stock debe ser un número entero.',
            'stock_nuevo.min' => 'El nuevo stock no puede ser negativo.',
            'stock_nuevo.max' => 'El nuevo stock supera el máximo permitido.',
            'motivo.required' => 'El motivo del ajuste es obligatorio.',
            'motivo.not_regex' => 'El motivo del ajuste no puede estar vacío.',
            'motivo.max' => 'El motivo no puede tener más de 255 caracteres.',
        ];
    }
}
