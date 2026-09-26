<?php
// Tokenizer & AST builder to accurately parse JS Object Literals into PHP arrays

class JsObjectParser {
    private string $src;
    private int $pos = 0;
    private int $len = 0;

    public function __construct(string $src) {
        $this->src = $src;
        $this->len = strlen($src);
    }

    private function skipWhitespace() {
        while ($this->pos < $this->len) {
            $c = $this->src[$this->pos];
            if (ctype_space($c)) {
                $this->pos++;
                continue;
            }
            // Skip single line comment
            if ($c === '/' && $this->pos + 1 < $this->len && $this->src[$this->pos + 1] === '/') {
                $this->pos += 2;
                while ($this->pos < $this->len && $this->src[$this->pos] !== "\n") {
                    $this->pos++;
                }
                continue;
            }
            // Skip multi-line comment
            if ($c === '/' && $this->pos + 1 < $this->len && $this->src[$this->pos + 1] === '*') {
                $this->pos += 2;
                while ($this->pos + 1 < $this->len && !($this->src[$this->pos] === '*' && $this->src[$this->pos + 1] === '/')) {
                    $this->pos++;
                }
                $this->pos += 2;
                continue;
            }
            break;
        }
    }

    private function peek(): ?string {
        $this->skipWhitespace();
        return $this->pos < $this->len ? $this->src[$this->pos] : null;
    }

    public function parseValue() {
        $this->skipWhitespace();
        if ($this->pos >= $this->len) return null;

        $c = $this->src[$this->pos];
        if ($c === '{') {
            return $this->parseObject();
        } elseif ($c === '[') {
            return $this->parseArray();
        } elseif ($c === "'" || $c === '"' || $c === '`') {
            return $this->parseString();
        } elseif (ctype_digit($c) || $c === '-') {
            return $this->parseNumber();
        } elseif (ctype_alpha($c) || $c === '_' || $c === '$') {
            $ident = $this->parseIdentifier();
            if ($ident === 'true') return true;
            if ($ident === 'false') return false;
            if ($ident === 'null') return null;
            return $ident;
        }
        throw new Exception("Unexpected character '$c' at pos {$this->pos}");
    }

    private function parseObject(): array {
        $this->pos++; // consume '{'
        $obj = [];
        while (true) {
            $this->skipWhitespace();
            if ($this->pos >= $this->len) break;
            if ($this->src[$this->pos] === '}') {
                $this->pos++;
                break;
            }

            // Parse key
            $key = null;
            $c = $this->src[$this->pos];
            if ($c === "'" || $c === '"' || $c === '`') {
                $key = $this->parseString();
            } else {
                $key = $this->parseIdentifier();
            }

            $this->skipWhitespace();
            if ($this->src[$this->pos] !== ':') {
                throw new Exception("Expected ':' after key '$key' at pos {$this->pos}");
            }
            $this->pos++; // consume ':'

            $val = $this->parseValue();
            $obj[$key] = $val;

            $this->skipWhitespace();
            if ($this->pos < $this->len && $this->src[$this->pos] === ',') {
                $this->pos++;
            } elseif ($this->pos < $this->len && $this->src[$this->pos] === '}') {
                $this->pos++;
                break;
            }
        }
        return $obj;
    }

    private function parseArray(): array {
        $this->pos++; // consume '['
        $arr = [];
        while (true) {
            $this->skipWhitespace();
            if ($this->pos >= $this->len) break;
            if ($this->src[$this->pos] === ']') {
                $this->pos++;
                break;
            }

            $arr[] = $this->parseValue();

            $this->skipWhitespace();
            if ($this->pos < $this->len && $this->src[$this->pos] === ',') {
                $this->pos++;
            } elseif ($this->pos < $this->len && $this->src[$this->pos] === ']') {
                $this->pos++;
                break;
            }
        }
        return $arr;
    }

    private function parseString(): string {
        $quote = $this->src[$this->pos];
        $this->pos++; // consume quote
        $str = '';
        while ($this->pos < $this->len) {
            $c = $this->src[$this->pos];
            if ($c === '\\') {
                $this->pos++;
                if ($this->pos >= $this->len) break;
                $esc = $this->src[$this->pos];
                if ($esc === 'n') $str .= "\n";
                elseif ($esc === 'r') $str .= "\r";
                elseif ($esc === 't') $str .= "\t";
                elseif ($esc === '\\') $str .= "\\";
                elseif ($esc === "'") $str .= "'";
                elseif ($esc === '"') $str .= '"';
                else $str .= $esc;
                $this->pos++;
                continue;
            }
            if ($c === $quote) {
                $this->pos++; // consume quote
                return $str;
            }
            $str .= $c;
            $this->pos++;
        }
        return $str;
    }

    private function parseIdentifier(): string {
        $ident = '';
        while ($this->pos < $this->len) {
            $c = $this->src[$this->pos];
            if (ctype_alnum($c) || $c === '_' || $c === '-' || $c === '$') {
                $ident .= $c;
                $this->pos++;
            } else {
                break;
            }
        }
        return $ident;
    }

    private function parseNumber(): float|int {
        $numStr = '';
        while ($this->pos < $this->len) {
            $c = $this->src[$this->pos];
            if (ctype_digit($c) || $c === '.' || $c === '-' || $c === '+' || $c === 'e' || $c === 'E') {
                $numStr .= $c;
                $this->pos++;
            } else {
                break;
            }
        }
        return strpos($numStr, '.') !== false ? (float)$numStr : (int)$numStr;
    }
}

// Test with our extracted blocks
require_once __DIR__ . '/test_matcher.php';

$js = file_get_contents(__DIR__ . '/public/js/script.js');

$locales = ['id', 'en-GB', 'en-US', 'en-CA', 'jv', 'ja', 'ar', 'ms'];
$masterDict = [];
foreach ($locales as $loc) {
    $masterDict[$loc] = [];
}

// 1. Parse languageDictionary
$dictBlock = extractJsBlock($js, 'const languageDictionary =');
$parser = new JsObjectParser($dictBlock);
$parsed = $parser->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed languageDictionary: " . count($parsed) . " langs\n";

// 2. welcomeTranslations
$b = extractJsBlock($js, 'const welcomeTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed welcomeTranslations: " . count($parsed) . " langs\n";

// 3. welcomeDetailsTranslations
$b = extractJsBlock($js, 'const welcomeDetailsTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed welcomeDetailsTranslations: " . count($parsed) . " langs\n";

// 4. sharedInterfaceTranslations
$b = extractJsBlock($js, 'const sharedInterfaceTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed sharedInterfaceTranslations: " . count($parsed) . " langs\n";

// 5. workspaceTranslations
$b = extractJsBlock($js, 'const workspaceTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed workspaceTranslations: " . count($parsed) . " langs\n";

// 6. farmDeleteTranslations (format: { id: '...', 'en-GB': '...' })
$b = extractJsBlock($js, 'const farmDeleteTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $val) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang]['farm-delete'] = $val;
    }
}
echo "Parsed farmDeleteTranslations\n";

// 7. emptyActivityTranslations
$b = extractJsBlock($js, 'const emptyActivityTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $val) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang]['farm-empty-activity'] = $val;
    }
}
echo "Parsed emptyActivityTranslations\n";

// 8. shellTranslations
$b = extractJsBlock($js, 'const shellTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed shellTranslations\n";

// 9. globalUiTranslations
$b = extractJsBlock($js, 'const globalUiTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed globalUiTranslations\n";

// 10. popupTranslations
$b = extractJsBlock($js, 'const popupTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed popupTranslations\n";

// 11. extraSurfaceTranslations
$b = extractJsBlock($js, 'const extraSurfaceTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed extraSurfaceTranslations\n";

// 12. environmentToastTranslations
$b = extractJsBlock($js, 'const environmentToastTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed environmentToastTranslations\n";

// 13. gardenWizardTranslations
$b = extractJsBlock($js, 'const gardenWizardTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed gardenWizardTranslations\n";

// 14. notificationTranslations
$b = extractJsBlock($js, 'const notificationTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed notificationTranslations\n";

// 15. detailExtraTranslations
$b = extractJsBlock($js, 'const detailExtraTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed detailExtraTranslations\n";

// 16. detailSurfaceTranslations
$b = extractJsBlock($js, 'const detailSurfaceTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed detailSurfaceTranslations\n";

// 17. connectionTranslations (id, en, ja, ar)
$b = extractJsBlock($js, 'const connectionTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($locales as $loc) {
    $connCopy = $parsed[$loc] ?? ($parsed['en'] ?? []);
    $masterDict[$loc] = array_merge($masterDict[$loc], $connCopy);
}
echo "Parsed connectionTranslations\n";

// 18. runtimeTranslations
$b = extractJsBlock($js, 'const runtimeTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed runtimeTranslations\n";

// 19. toastTranslations
$b = extractJsBlock($js, 'const toastTranslations =');
$parsed = (new JsObjectParser($b))->parseValue();
foreach ($parsed as $lang => $keys) {
    if (isset($masterDict[$lang])) {
        $masterDict[$lang] = array_merge($masterDict[$lang], $keys);
    }
}
echo "Parsed toastTranslations\n";

// 20. metric-definition and metric-effect
$metricDef = [
    'en-GB' => ['metric-definition' => 'Definition', 'metric-effect' => 'Impact on engine'],
    'en-US' => ['metric-definition' => 'Definition', 'metric-effect' => 'Impact on engine'],
    'en-CA' => ['metric-definition' => 'Definition', 'metric-effect' => 'Impact on engine'],
    'id' => ['metric-definition' => 'Definisi', 'metric-effect' => 'Pengaruh terhadap analisis mesin'],
    'jv' => ['metric-definition' => 'Definisi', 'metric-effect' => 'Pengaruh marang analisis mesin'],
    'ja' => ['metric-definition' => '定義', 'metric-effect' => '分析エンジンへの影響'],
    'ar' => ['metric-definition' => 'التعريف', 'metric-effect' => 'التأثير على محرك التحليل'],
    'ms' => ['metric-definition' => 'Definisi', 'metric-effect' => 'Kesan terhadap enjin analisis'],
];
foreach ($metricDef as $loc => $pairs) {
    $masterDict[$loc] = array_merge($masterDict[$loc], $pairs);
}

// 21. Also merge existing keys in lang/*.json so no keys are lost
foreach ($locales as $loc) {
    $existingFile = __DIR__ . "/lang/{$loc}.json";
    if (file_exists($existingFile)) {
        $existingData = json_decode(file_get_contents($existingFile), true);
        if (is_array($existingData)) {
            $masterDict[$loc] = array_merge($masterDict[$loc], $existingData);
        }
    }
    echo "Total keys for $loc: " . count($masterDict[$loc]) . "\n";
}

// Save to lang/*.json
foreach ($locales as $loc) {
    ksort($masterDict[$loc]);
    $outPath = __DIR__ . "/lang/{$loc}.json";
    file_put_contents($outPath, json_encode($masterDict[$loc], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    echo "Saved $loc to lang/{$loc}.json (" . count($masterDict[$loc]) . " keys)\n";
}
