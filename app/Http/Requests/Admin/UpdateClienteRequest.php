<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateClienteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeRequiredString('nombre');

        foreach (['apellido', 'email', 'telefono', 'direccion', 'notas'] as $field) {
            $this->normalizeOptionalString($field);
        }
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

        if ($field === 'email') {
            $value = strtolower($value);
        }

        $this->merge([
            $field => $value === '' ? null : $value,
        ]);
    }

    public function authorize(): bool
    {
        $cliente = $this->route('cliente');

        return $cliente instanceof Cliente
            && ($this->user()?->can('update', $cliente) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $cliente = $this->route('cliente');

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\(\)\s]+$/'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('clientes', 'email')->ignore($cliente?->getKey()),
            ],
            'direccion' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today', 'after:'.now()->subYears(120)->format('Y-m-d')],
            'estado' => ['required', 'in:activo,inactivo'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del cliente es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'apellido.max' => 'El apellido no puede tener más de 255 caracteres.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'Este email ya está registrado por otro cliente.',
            'email.max' => 'El email no puede tener más de 255 caracteres.',
            'telefono.regex' => 'El formato del teléfono no es válido. Solo números, +, -, ( ) y espacios.',
            'telefono.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'direccion.max' => 'La dirección no puede tener más de 255 caracteres.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento no puede ser futura.',
            'fecha_nacimiento.after' => 'La fecha de nacimiento no puede ser mayor a 120 años.',
            'estado.required' => 'Debes seleccionar el estado del cliente.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
            'notas.max' => 'Las notas no pueden tener más de 1000 caracteres.',
        ];
    }
}
