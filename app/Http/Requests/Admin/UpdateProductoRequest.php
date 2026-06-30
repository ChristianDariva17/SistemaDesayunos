<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProductoRequest extends FormRequest
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
        $producto = $this->route('producto');

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'categoria' => ['required', 'string', 'max:100'],
            'precio' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'codigo_barras' => [
                'nullable',
                'string',
                'max:50',
                'unique:productos,codigo_barras,' . ($producto?->getKey() ?? 'NULL'),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:50',
                'unique:productos,sku,' . ($producto?->getKey() ?? 'NULL'),
            ],
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'estado' => ['required', 'in:activo,inactivo'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'descripcion.max' => 'La descripción no puede tener más de 1000 caracteres.',
            'categoria.required' => 'La categoría del producto es obligatoria.',
            'categoria.max' => 'La categoría no puede tener más de 100 caracteres.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número válido.',
            'precio.min' => 'El precio no puede ser negativo.',
            'precio.max' => 'El precio no puede superar los 999,999.99.',
            'codigo_barras.unique' => 'Este código de barras ya está registrado.',
            'codigo_barras.max' => 'El código de barras no puede tener más de 50 caracteres.',
            'sku.unique' => 'Este SKU ya está registrado.',
            'sku.max' => 'El SKU no puede tener más de 50 caracteres.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock no puede ser negativo.',
            'stock.max' => 'El stock no puede superar las 999,999 unidades.',
            'estado.required' => 'Debes seleccionar el estado del producto.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser formato: jpeg, png, jpg, gif o webp.',
            'imagen.max' => 'La imagen no puede pesar más de 2MB.',
        ];
    }
}
