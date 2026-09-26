<?php
$js = file_get_contents(__DIR__ . '/public/js/script.js');

// Step 1: Replace line 8 to 582 with the new languageDictionary and architectureTranslations
$startMarker = "    const languageDictionary = {";
$endMarker = "    // Expose dictionary globally so inline scripts (simulator etc.) can access it\n    window.languageDictionary = languageDictionary;";

$posStart = strpos($js, $startMarker);
$posEnd = strpos($js, $endMarker);

if ($posStart === false || $posEnd === false) {
    die("Markers not found!\n");
}

// Read the architectureTranslations block
require_once __DIR__ . '/test_matcher.php';
$archBlock = extractJsBlock($js, 'const architectureTranslations =');
if (!$archBlock) {
    die("Architecture block not found!\n");
}

$newBlock = <<<JS
    const languageDictionary = {
        'id': {},
        'en-GB': {},
        'en-US': {},
        'en-CA': {},
        'jv': {},
        'ja': {},
        'ar': {},
        'ms': {}
    };

    const architectureTranslations = $archBlock;

JS;

$newJs = substr($js, 0, $posStart) . $newBlock . substr($js, $posEnd);

// Step 2: Now update applyLanguage and loadNutrixLocaleCatalog
// Let's check how applyLanguage and loadNutrixLocaleCatalog are currently written in $newJs:
$oldCatalogFn = <<<JS
    window.loadNutrixLocaleCatalog = async function (language = window.currentLanguage || 'id') {
        const validLanguage = languageDictionary[language] ? language : 'id';
        try {
            const response = await fetch(`/locales/\${encodeURIComponent(validLanguage)}.json`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });
            if (!response.ok) return false;
            const catalog = await response.json();
            Object.assign(languageDictionary[validLanguage], catalog);
            if (window.currentLanguage === validLanguage) applyLanguage(validLanguage);
            document.dispatchEvent(new CustomEvent('nutrix:catalogloaded', { detail: { language: validLanguage } }));
            return true;
        } catch (error) {
            console.warn('NUTRIX locale catalog could not be loaded.', error);
            return false;
        }
    };

    applyTheme(localStorage.getItem('nutrix_theme') || 'emerald');
    applyLanguage(localStorage.getItem('nutrix_language') || 'id');
    window.loadNutrixLocaleCatalog(window.currentLanguage);
JS;

$newCatalogFn = <<<JS
    window.loadNutrixLocaleCatalog = async function (language = window.currentLanguage || 'id') {
        const validLanguage = languageDictionary[language] ? language : 'id';
        try {
            const response = await fetch(`/locales/\${encodeURIComponent(validLanguage)}.json`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });
            if (!response.ok) return false;
            const catalog = await response.json();
            Object.assign(languageDictionary[validLanguage], catalog);
            if (window.currentLanguage === validLanguage) applyLanguage(validLanguage);
            document.dispatchEvent(new CustomEvent('nutrix:catalogloaded', { detail: { language: validLanguage } }));
            return true;
        } catch (error) {
            console.warn('NUTRIX locale catalog could not be loaded.', error);
            return false;
        }
    };

    async function changeLanguage(lang) {
        const valid = languageDictionary[lang] ? lang : 'id';
        if (!languageDictionary[valid] || Object.keys(languageDictionary[valid]).length === 0) {
            await window.loadNutrixLocaleCatalog(valid);
        }
        applyLanguage(valid);
    }

    applyTheme(localStorage.getItem('nutrix_theme') || 'emerald');
    const initialLang = localStorage.getItem('nutrix_language') || 'id';
    window.loadNutrixLocaleCatalog(initialLang).then(() => {
        applyLanguage(initialLang);
    });
JS;

if (strpos($newJs, $oldCatalogFn) !== false) {
    $newJs = str_replace($oldCatalogFn, $newCatalogFn, $newJs);
    echo "Replaced loadNutrixLocaleCatalog initialization.\n";
} else {
    echo "WARNING: oldCatalogFn string not matched exactly, checking...\n";
}

// Step 3: Check languageButton click listener
$oldLangClick = "if (languageButton) {\n            event.preventDefault();\n            applyLanguage(languageButton.dataset.lang);\n        }";
$newLangClick = "if (languageButton) {\n            event.preventDefault();\n            changeLanguage(languageButton.dataset.lang);\n        }";
if (strpos($newJs, $oldLangClick) !== false) {
    $newJs = str_replace($oldLangClick, $newLangClick, $newJs);
    echo "Replaced languageButton click handler with changeLanguage.\n";
} else {
    echo "WARNING: oldLangClick string not matched exactly.\n";
}

// Step 4: Storage event listener
$oldStorage = "if (event.key === 'nutrix_language') applyLanguage(event.newValue || 'id');";
$newStorage = "if (event.key === 'nutrix_language') changeLanguage(event.newValue || 'id');";
if (strpos($newJs, $oldStorage) !== false) {
    $newJs = str_replace($oldStorage, $newStorage, $newJs);
    echo "Replaced storage event listener with changeLanguage.\n";
}

file_put_contents(__DIR__ . '/public/js/script.js', $newJs);
echo "Successfully updated public/js/script.js! New size: " . strlen($newJs) . " bytes\n";
