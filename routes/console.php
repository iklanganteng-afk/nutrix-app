<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('translations:validate', function () {
    $files = collect(File::files(base_path('lang')))
        ->filter(fn ($file) => $file->getExtension() === 'json')
        ->sortBy(fn ($file) => $file->getFilename());

    if ($files->isEmpty()) {
        $this->error('No locale JSON files found in lang/.');
        return 1;
    }

    $catalogs = [];
    foreach ($files as $file) {
        $locale = $file->getBasename('.json');
        $catalog = json_decode(File::get($file->getPathname()), true);
        if (!is_array($catalog)) {
            $this->error("Invalid JSON: {$file->getFilename()}");
            return 1;
        }
        $catalogs[$locale] = $catalog;
    }

    $referenceLocale = array_key_first($catalogs);
    $referenceKeys = array_keys($catalogs[$referenceLocale]);
    $failed = false;

    foreach ($catalogs as $locale => $catalog) {
        $keys = array_keys($catalog);
        $missing = array_diff($referenceKeys, $keys);
        $extra = array_diff($keys, $referenceKeys);
        $empty = array_keys(array_filter($catalog, fn ($value) => !is_string($value) || trim($value) === ''));

        if ($missing || $extra || $empty) {
            $failed = true;
            $this->error("{$locale}: " . json_encode([
                'missing' => array_values($missing),
                'extra' => array_values($extra),
                'empty' => array_values($empty),
            ], JSON_UNESCAPED_UNICODE));
        }
    }

    if ($failed) {
        return 1;
    }

    $this->info(count($catalogs) . ' locale catalogs validated with ' . count($referenceKeys) . ' keys.');
    return 0;
})->purpose('Validate translation keys across all locale catalogs');
