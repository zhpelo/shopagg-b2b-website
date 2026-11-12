<?php
declare(strict_types=1);

namespace App\Core;

final class ThemeMetadata {
    private const FIELDS = [
        'Theme Name' => 'name',
        'Theme URI' => 'theme_uri',
        'Author' => 'author',
        'Author URI' => 'author_uri',
        'Description' => 'description',
        'Version' => 'version',
        'License' => 'license',
        'Language' => 'language',
        'Text Direction' => 'text_direction',
    ];

    public static function read(string $styleFile): array {
        $metadata = array_fill_keys(self::FIELDS, '');
        if (!is_file($styleFile) || !is_readable($styleFile)) {
            return $metadata;
        }

        $content = file_get_contents($styleFile, false, null, 0, 8192);
        if ($content === false) {
            return $metadata;
        }

        foreach (self::FIELDS as $label => $key) {
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($label, '/') . ':[ \t]*([^\r\n]*)/mi', $content, $matches) === 1) {
                $metadata[$key] = trim($matches[1]);
            }
        }
        return $metadata;
    }
}
