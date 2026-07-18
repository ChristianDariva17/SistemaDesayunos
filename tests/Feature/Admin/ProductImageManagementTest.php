<?php

declare(strict_types=1);

use App\Http\Controllers\Trabajador\ProductoController as TrabajadorProductoController;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

function productImageUpload(string $name = 'product.png', int $width = 1, int $height = 1): UploadedFile
{
    $chunk = static fn (string $type, string $data): string => pack('N', strlen($data))
        .$type.$data.pack('N', crc32($type.$data));
    $header = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);
    $pixels = str_repeat("\0", 1 + ($width * 3 * $height));
    $png = "\x89PNG\r\n\x1a\n"
        .$chunk('IHDR', $header)
        .$chunk('IDAT', gzcompress($pixels))
        .$chunk('IEND', '');

    return UploadedFile::fake()->createWithContent($name, $png);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function productImagePayload(array $overrides = []): array
{
    return array_merge([
        'nombre' => 'Image product',
        'categoria' => 'breakfast',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ], $overrides);
}

/** @param array<string, mixed> $overrides */
function productWithImage(array $overrides = []): Producto
{
    return Producto::create(productImagePayload(array_merge([
        'nombre' => 'Existing image product',
        'imagen' => 'productos/original.png',
    ], $overrides)));
}

beforeEach(function (): void {
    Storage::fake('public');
    $this->admin = User::factory()->create(['rol' => 'administrador']);
});

it('stores a validated product image on create', function (): void {
    $response = $this->actingAs($this->admin)->post(
        route('admin.productos.store'),
        productImagePayload(['imagen' => productImageUpload()]),
    );

    $response->assertRedirect(route('admin.productos.index'))->assertSessionHasNoErrors();
    $path = Producto::query()->where('nombre', 'Image product')->value('imagen');
    expect($path)->toBeString()->toStartWith('productos/');
    Storage::disk('public')->assertExists($path);
});

it('rejects product images above the dimension limit', function (): void {
    $this->actingAs($this->admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), productImagePayload([
            'imagen' => productImageUpload(width: 4097),
        ]))
        ->assertRedirect(route('admin.productos.create'))
        ->assertSessionHasErrors('imagen');

    expect(Storage::disk('public')->allFiles('productos'))->toBe([]);
});

it('preserves the current image when an update has no image instruction', function (): void {
    $producto = productWithImage();
    Storage::disk('public')->put($producto->imagen, 'original');

    $this->actingAs($this->admin)
        ->put(route('admin.productos.update', $producto), productImagePayload(['nombre' => 'Updated']))
        ->assertRedirect(route('admin.productos.show', $producto));

    expect($producto->fresh()->imagen)->toBe('productos/original.png');
    Storage::disk('public')->assertExists('productos/original.png');
});

it('replaces the current image and gives upload precedence over removal', function (): void {
    $producto = productWithImage();
    Storage::disk('public')->put($producto->imagen, 'original');

    $this->actingAs($this->admin)->put(
        route('admin.productos.update', $producto),
        productImagePayload(['imagen' => productImageUpload('replacement.png'), 'eliminar_imagen' => '1']),
    )->assertRedirect(route('admin.productos.show', $producto));

    $newPath = $producto->fresh()->imagen;
    expect($newPath)->not->toBeNull()->not->toBe('productos/original.png');
    Storage::disk('public')->assertExists($newPath);
    Storage::disk('public')->assertMissing('productos/original.png');
});

it('removes the current image only after persisting the explicit removal', function (): void {
    $producto = productWithImage();
    Storage::disk('public')->put($producto->imagen, 'original');

    $this->actingAs($this->admin)->put(
        route('admin.productos.update', $producto),
        productImagePayload(['eliminar_imagen' => '1']),
    )->assertRedirect(route('admin.productos.show', $producto));

    expect($producto->fresh()->imagen)->toBeNull();
    Storage::disk('public')->assertMissing('productos/original.png');
});

it('cleans new files when create or update persistence fails', function (): void {
    DB::unprepared("CREATE TRIGGER fail_product_insert BEFORE INSERT ON productos BEGIN SELECT RAISE(ABORT, 'forced insert failure'); END");
    $this->actingAs($this->admin)->post(
        route('admin.productos.store'),
        productImagePayload(['imagen' => productImageUpload('create.png')]),
    )->assertSessionHas('error');
    DB::unprepared('DROP TRIGGER fail_product_insert');

    $producto = productWithImage();
    Storage::disk('public')->put($producto->imagen, 'original');
    DB::unprepared("CREATE TRIGGER fail_product_update BEFORE UPDATE ON productos BEGIN SELECT RAISE(ABORT, 'forced update failure'); END");
    $this->actingAs($this->admin)->put(
        route('admin.productos.update', $producto),
        productImagePayload(['imagen' => productImageUpload('update.png')]),
    )->assertSessionHas('error');

    expect(Storage::disk('public')->allFiles('productos'))->toBe(['productos/original.png'])
        ->and($producto->fresh()->imagen)->toBe('productos/original.png');
});

it('deletes the database row before cleaning its image', function (): void {
    $producto = productWithImage();
    Storage::disk('public')->put($producto->imagen, 'original');

    $this->actingAs($this->admin)
        ->delete(route('admin.productos.destroy', $producto))
        ->assertRedirect(route('admin.productos.index'));

    $this->assertDatabaseMissing('productos', ['id' => $producto->id]);
    Storage::disk('public')->assertMissing('productos/original.png');
});

it('copies images for duplication and cleans the copy when saving fails', function (): void {
    $producto = productWithImage();
    Storage::disk('public')->put($producto->imagen, 'original');

    $this->actingAs($this->admin)->post(route('admin.productos.duplicar', $producto));
    $copy = Producto::query()->whereKeyNot($producto->id)->firstOrFail();
    expect($copy->imagen)->not->toBe($producto->imagen);
    Storage::disk('public')->assertExists($copy->imagen);

    DB::unprepared("CREATE TRIGGER fail_product_duplicate BEFORE INSERT ON productos BEGIN SELECT RAISE(ABORT, 'forced duplicate failure'); END");
    $filesBeforeFailure = Storage::disk('public')->allFiles('productos');
    $this->actingAs($this->admin)->post(route('admin.productos.duplicar', $producto))->assertSessionHas('error');
    expect(Storage::disk('public')->allFiles('productos'))->toBe($filesBeforeFailure);
});

it('does not write images for unauthorized product mutations', function (): void {
    $worker = User::factory()->create(['rol' => 'trabajador']);
    $producto = productWithImage();

    $this->actingAs($worker)
        ->post(route('admin.productos.store'), productImagePayload(['imagen' => productImageUpload()]))
        ->assertForbidden();
    $this->actingAs($worker)->post(route('admin.productos.duplicar', $producto))->assertForbidden();

    expect(Storage::disk('public')->allFiles('productos'))->toBe([]);
});

it('renders placeholders instead of stale product image requests', function (): void {
    $missingImage = productWithImage(['nombre' => 'Missing image product', 'categoria' => 'comidas']);
    $withoutImage = productWithImage(['nombre' => 'No image product', 'categoria' => 'comidas', 'imagen' => null]);
    $missingUrl = asset('storage/'.$missingImage->imagen);

    foreach ([
        route('admin.productos.index'),
        route('admin.productos.show', $missingImage),
        route('admin.productos.edit', $missingImage),
    ] as $url) {
        $this->actingAs($this->admin)->get($url)
            ->assertOk()
            ->assertDontSee($missingUrl, false)
            ->assertDontSee(asset('images/no-image.png'), false);
    }

    $worker = User::factory()->create(['rol' => 'trabajador']);

    foreach ([
        route('trabajador.productos.index'),
        route('trabajador.productos.show', $missingImage),
    ] as $url) {
        $this->actingAs($worker)->get($url)
            ->assertOk()
            ->assertDontSee($missingUrl, false);
    }

    expect($withoutImage->imagen)->toBeNull();
});

it('preserves stored product image URLs and list image attributes', function (): void {
    $producto = productWithImage(['categoria' => 'comidas']);
    Storage::disk('public')->put($producto->imagen, 'original');
    $imageUrl = asset('storage/'.$producto->imagen);

    $adminIndex = $this->actingAs($this->admin)->get(route('admin.productos.index'));
    $adminIndex->assertOk()->assertSee($imageUrl, false);

    expect($adminIndex->getContent())->toMatch('/src="'.preg_quote($imageUrl, '/').'"[^>]*width="50"[^>]*height="50"[^>]*loading="lazy"/s');

    foreach ([
        route('admin.productos.show', $producto),
        route('admin.productos.edit', $producto),
    ] as $url) {
        $this->actingAs($this->admin)->get($url)
            ->assertOk()
            ->assertSee($imageUrl, false);
    }

    $worker = User::factory()->create(['rol' => 'trabajador']);
    $workerIndex = $this->actingAs($worker)->get(route('trabajador.productos.index'));
    $workerIndex->assertOk()->assertSee($imageUrl, false);
    $workerIndexHtml = $workerIndex->getContent();

    expect($workerIndexHtml)->toMatch('/src="'.preg_quote($imageUrl, '/').'"[^>]*width="50"[^>]*height="50"[^>]*loading="lazy"/s');
});

it('returns null image URLs from product searches when stored files are missing', function (): void {
    $producto = productWithImage(['nombre' => 'Missing searchable image']);

    $this->actingAs($this->admin)
        ->getJson(route('admin.productos.buscar', ['q' => 'Missing searchable']))
        ->assertOk()
        ->assertJsonPath('productos.0.id', $producto->id)
        ->assertJsonPath('productos.0.imagen_url', null);

    $worker = User::factory()->create(['rol' => 'trabajador']);
    $this->actingAs($worker);
    $response = app(TrabajadorProductoController::class)->buscar(
        Request::create('/trabajador/productos/buscar', 'GET', ['q' => 'Missing searchable']),
    );

    expect($response->getData(true)['productos'][0]['imagen_url'])->toBeNull();
});
