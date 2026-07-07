<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Empleado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateEmpleadoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['nombre', 'rol_operativo', 'estado'] as $field) {
            $this->normalizeRequiredString($field);
        }

        foreach (['telefono', 'observaciones'] as $field) {
            $this->normalizeOptionalString($field);
        }

        $this->normalizeOptionalUserId();
    }

    private function normalizeRequiredString(string $field): void
    {
        if (! $this->has($field)) {
            return;
        }

        $value = $this->input($field);

        if (! is_string($value)) {
            return;
        }

        $this->merge([
            $field => trim($value),
        ]);
    }

    private function normalizeOptionalString(string $field): void
    {
        if (! $this->has($field)) {
            return;
        }

        $value = $this->input($field);

        if (! is_string($value)) {
            return;
        }

        $value = trim($value);

        $this->merge([
            $field => $value === '' ? null : $value,
        ]);
    }

    private function normalizeOptionalUserId(): void
    {
        if (! $this->has('user_id')) {
            return;
        }

        $value = $this->input('user_id');

        if ($value === '') {
            $this->merge(['user_id' => null]);
        }
    }

    public function authorize(): bool
    {
        $empleado = $this->route('empleado');

        return $empleado instanceof Empleado
            && ($this->user()?->can('update', $empleado) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $empleado = $this->route('empleado');

        return [
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                Rule::unique('empleados', 'user_id')->ignore($empleado?->getKey()),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'rol_operativo' => ['required', 'in:mesero,cajero,cocinero,chef,ayudante,otros'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string'],
            'estado' => ['required', 'in:activo,inactivo'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'El usuario seleccionado no es válido.',
            'user_id.unique' => 'Ese usuario ya está asociado a otro empleado.',
            'nombre.required' => 'El nombre del empleado es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'rol_operativo.required' => 'Debes seleccionar un rol para el empleado.',
            'rol_operativo.in' => 'El rol seleccionado no es válido.',
            'telefono.max' => 'El teléfono no puede tener más de 255 caracteres.',
            'estado.required' => 'Debes seleccionar el estado del empleado.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
