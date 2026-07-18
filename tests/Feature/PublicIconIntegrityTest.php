<?php

declare(strict_types=1);

it('ships structurally valid public favicon and touch icon assets', function (): void {
    $favicon = file_get_contents(public_path('favicon.ico'));

    expect($favicon)->toBeString()
        ->and(strlen($favicon))->toBeGreaterThan(0);

    $header = unpack('vreserved/vtype/vcount', substr($favicon, 0, 6));

    expect($header)->toBe([
        'reserved' => 0,
        'type' => 1,
        'count' => 3,
    ]);

    $frameSizes = [];

    for ($index = 0; $index < $header['count']; $index++) {
        $entry = unpack(
            'Cwidth/Cheight/Ccolors/Creserved/vplanes/vbits/Vbytes/Voffset',
            substr($favicon, 6 + ($index * 16), 16),
        );
        $frame = substr($favicon, $entry['offset'], $entry['bytes']);
        $imageInfo = getimagesizefromstring($frame);

        expect($entry['width'])->toBe($entry['height'])
            ->and($entry['planes'])->toBe(1)
            ->and($entry['bits'])->toBe(32)
            ->and(strlen($frame))->toBe($entry['bytes'])
            ->and($imageInfo)->toBeArray()
            ->and($imageInfo[0])->toBe($entry['width'])
            ->and($imageInfo[1])->toBe($entry['height'])
            ->and($imageInfo['mime'])->toBe('image/png');

        $frameSizes[] = $entry['width'];
    }

    expect($frameSizes)->toBe([16, 32, 48]);

    $touchIconPath = public_path('apple-touch-icon.png');
    $touchIcon = file_get_contents($touchIconPath);
    $touchIconInfo = getimagesize($touchIconPath);

    expect($touchIcon)->toBeString()
        ->and(strlen($touchIcon))->toBeGreaterThan(0)
        ->and($touchIconInfo)->toBeArray()
        ->and($touchIconInfo[0])->toBe(180)
        ->and($touchIconInfo[1])->toBe(180)
        ->and($touchIconInfo['mime'])->toBe('image/png')
        ->and(ord($touchIcon[25]))->toBe(2)
        ->and($touchIcon)->not->toContain('tRNS');
});
