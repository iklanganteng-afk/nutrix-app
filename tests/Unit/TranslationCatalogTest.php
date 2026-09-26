<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TranslationCatalogTest extends TestCase
{
    public function test_all_locale_catalogs_have_the_same_non_empty_keys(): void
    {
        $locales = ['id', 'en-GB', 'en-US', 'en-CA', 'jv', 'ja', 'ar', 'ms'];
        $catalogs = [];

        foreach ($locales as $locale) {
            $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $locale . '.json';
            $this->assertFileExists($path);
            $catalogs[$locale] = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        }

        $referenceKeys = array_keys($catalogs['id']);
        sort($referenceKeys);

        foreach ($catalogs as $locale => $catalog) {
            $keys = array_keys($catalog);
            sort($keys);
            $this->assertSame($referenceKeys, $keys, "Locale {$locale} does not match the canonical key set.");
            foreach ($catalog as $key => $value) {
                $this->assertIsString($value, "Locale {$locale} key {$key} must contain a string.");
                $this->assertNotSame('', trim($value), "Locale {$locale} key {$key} must not be empty.");
            }
        }
    }
}
