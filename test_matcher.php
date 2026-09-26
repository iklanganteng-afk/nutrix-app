<?php
$js = file_get_contents(__DIR__ . '/public/js/script.js');

// Helper to convert JS object syntax to valid JSON
function jsObjectToJson($jsStr) {
    // Replace single quotes with double quotes, handling escaped quotes
    // But be careful with words like don't, can't
    // Better approach: token-based or regex parser
}

// Alternatively, let's extract each const XXX = { ... }; using brace matching!
function extractJsBlock($source, $startVar) {
    $pos = strpos($source, $startVar);
    if ($pos === false) return null;
    $braceStart = strpos($source, '{', $pos);
    if ($braceStart === false) return null;
    
    $len = strlen($source);
    $depth = 0;
    $inString = false;
    $stringChar = '';
    $escaped = false;
    
    for ($i = $braceStart; $i < $len; $i++) {
        $char = $source[$i];
        if ($escaped) {
            $escaped = false;
            continue;
        }
        if ($char === '\\') {
            $escaped = true;
            continue;
        }
        if ($inString) {
            if ($char === $stringChar) {
                $inString = false;
            }
            continue;
        }
        if ($char === "'" || $char === '"' || $char === '`') {
            $inString = true;
            $stringChar = $char;
            continue;
        }
        if ($char === '{') {
            $depth++;
        } elseif ($char === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($source, $braceStart, $i - $braceStart + 1);
            }
        }
    }
    return null;
}

$vars = [
    'const languageDictionary =',
    'const welcomeTranslations =',
    'const welcomeDetailsTranslations =',
    'const sharedInterfaceTranslations =',
    'const workspaceTranslations =',
    'const farmDeleteTranslations =',
    'const emptyActivityTranslations =',
    'const shellTranslations =',
    'const globalUiTranslations =',
    'const popupTranslations =',
    'const extraSurfaceTranslations =',
    'const environmentToastTranslations =',
    'const gardenWizardTranslations =',
    'const notificationTranslations =',
    'const detailExtraTranslations =',
    'const detailSurfaceTranslations =',
    'const connectionTranslations =',
    'const runtimeTranslations =',
    'const toastTranslations ='
];

foreach ($vars as $v) {
    $block = extractJsBlock($js, $v);
    echo "$v: " . ($block ? "Matched (" . strlen($block) . " chars)" : "FAILED") . "\n";
}
