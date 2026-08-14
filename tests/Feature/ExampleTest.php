<?php

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

test('all project json files are structurally valid', function () {
    // 1. Configure Finder to ignore massive directories at scan time
    $finder = (new Finder())
        ->in(base_path())
        ->name('*.json')
        ->exclude(['vendor', 'node_modules', 'storage', '.git']) // Skipped before loading
        ->files();

    // 2. Iterate safely without memory overload
    foreach ($finder as $file) {
        json_decode(
            $file->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        expect(true)->toBeTrue();
    }
});