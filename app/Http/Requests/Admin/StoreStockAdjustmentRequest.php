<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\InventoryLimits;
use Illuminate\Foundation\Http\FormRequest;

final class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'stock_nuevo' => ['required', 'integer', 'min:0', 'max:' . InventoryLimits::MAX_STOCK_LEVEL],
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
