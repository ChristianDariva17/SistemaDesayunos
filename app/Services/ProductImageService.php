<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class ProductImageService
{
    private const DIRECTORY = 'productos';

    public function store(UploadedFile $image): string
    {
        $path = $image->store(self::DIRECTORY, 'public');

        if (! is_string($path) || ! $this->isManagedPath($path) || ! Storage::disk('public')->exists($path)) {
            throw new RuntimeException('The product image could not be stored.');
        }

        return $path;
    }

    public function copy(?string $sourcePath): ?string
    {
        if ($sourcePath === null) {
            return null;
        }

        if (! $this->isManagedPath($sourcePath)) {
            throw new RuntimeException('Refusing to copy an unmanaged product image.');
        }

        if (! Storage::disk('public')->exists($sourcePath)) {
            return null;
        }

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $destination = self::DIRECTORY.'/'.Str::uuid().($extension !== '' ? '.'.$extension : '');

        if (! Storage::disk('public')->copy($sourcePath, $destination)
            || ! Storage::disk('public')->exists($destination)) {
            $this->delete($destination);

            throw new RuntimeException('The product image could not be copied.');
        }

        return $destination;
    }

    public function delete(?string $path): void
    {
        if ($path === null) {
            return;
        }

        if (! $this->isManagedPath($path)) {
            throw new RuntimeException('Refusing to delete an unmanaged product image.');
        }

        if (! Storage::disk('public')->delete($path) || Storage::disk('public')->exists($path)) {
            throw new RuntimeException('The product image could not be deleted.');
        }
    }

    private function isManagedPath(string $path): bool
    {
        return preg_match('#\Aproductos/(?!\.{1,2}(?:/|\z))(?!.*(?:/\.{1,2})(?:/|\z))(?!.*//)[A-Za-z0-9][A-Za-z0-9._/-]*\z#', $path) === 1;
    }
}
