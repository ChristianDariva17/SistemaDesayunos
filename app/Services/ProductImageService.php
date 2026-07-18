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

    private const THUMBNAIL_DIRECTORY = self::DIRECTORY.'/thumbnails';

    private const THUMBNAIL_SIZE = 160;

    public function store(UploadedFile $image): string
    {
        $path = $image->store(self::DIRECTORY, 'public');

        if (! is_string($path) || ! $this->isManagedPath($path) || ! Storage::disk('public')->exists($path)) {
            throw new RuntimeException('The product image could not be stored.');
        }

        try {
            $this->generateThumbnail($path);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
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

        try {
            $this->generateThumbnail($destination);
        } catch (\Throwable $exception) {
            $this->delete($destination);

            throw $exception;
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

        $thumbnailPath = self::thumbnailPath($path);

        if (! Storage::disk('public')->delete([$path, $thumbnailPath])
            || Storage::disk('public')->exists($path)
            || Storage::disk('public')->exists($thumbnailPath)) {
            throw new RuntimeException('The product image could not be deleted.');
        }
    }

    public static function thumbnailPath(string $path): string
    {
        return self::THUMBNAIL_DIRECTORY.'/'.hash('sha256', $path).'.jpg';
    }

    public function generateThumbnail(string $path): bool
    {
        if (! $this->isManagedPath($path)) {
            throw new RuntimeException('Refusing to process an unmanaged product image.');
        }

        $disk = Storage::disk('public');
        $thumbnailPath = self::thumbnailPath($path);

        if (! $disk->exists($path)) {
            $disk->delete($thumbnailPath);

            return false;
        }

        $contents = $disk->get($path);
        $dimensions = @getimagesizefromstring($contents);
        $source = @imagecreatefromstring($contents);

        if ($dimensions === false || $source === false) {
            throw new RuntimeException('The product image could not be decoded.');
        }

        [$width, $height] = $dimensions;
        $scale = min(self::THUMBNAIL_SIZE / $width, self::THUMBNAIL_SIZE / $height, 1);
        $thumbnailWidth = max(1, (int) floor($width * $scale));
        $thumbnailHeight = max(1, (int) floor($height * $scale));

        if (($thumbnailWidth * $thumbnailHeight) >= ($width * $height)) {
            imagedestroy($source);
            $disk->delete($thumbnailPath);

            return false;
        }

        $thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

        if ($thumbnail === false) {
            imagedestroy($source);

            throw new RuntimeException('The product thumbnail canvas could not be created.');
        }

        imagefill($thumbnail, 0, 0, imagecolorallocate($thumbnail, 255, 255, 255));
        imagecopyresampled(
            $thumbnail,
            $source,
            0,
            0,
            0,
            0,
            $thumbnailWidth,
            $thumbnailHeight,
            $width,
            $height,
        );

        ob_start();
        $encoded = imagejpeg($thumbnail, null, 78);
        $thumbnailContents = ob_get_clean();
        imagedestroy($thumbnail);
        imagedestroy($source);

        if (! $encoded) {
            throw new RuntimeException('The product thumbnail could not be encoded.');
        }

        if (strlen($thumbnailContents) >= strlen($contents)) {
            $disk->delete($thumbnailPath);

            return false;
        }

        if (! $disk->put($thumbnailPath, $thumbnailContents) || ! $disk->exists($thumbnailPath)) {
            throw new RuntimeException('The product thumbnail could not be stored.');
        }

        return true;
    }

    private function isManagedPath(string $path): bool
    {
        return preg_match('#\Aproductos/(?!\.{1,2}(?:/|\z))(?!.*(?:/\.{1,2})(?:/|\z))(?!.*//)[A-Za-z0-9][A-Za-z0-9._/-]*\z#', $path) === 1;
    }
}
