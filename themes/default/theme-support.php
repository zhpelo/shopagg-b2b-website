<?php
declare(strict_types=1);

/** Theme-local helpers; theme packages remain independently installable. */
if (!function_exists('lighthouse_theme_href')) {
    function lighthouse_theme_href(?string $value, string $fallback = '#'): string {
        $value = trim((string)$value);
        if ($value === '') $value = $fallback;
        $value = preg_replace('/[\x00-\x20\x7f]/', '', $value) ?? '';
        if (str_contains($value, '\\')) return '#';
        if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $value) && !preg_match('#^(https?://|mailto:|tel:)#i', $value)) return '#';
        if (str_starts_with($value, '#') || preg_match('#^(https?:)?//#i', $value) || preg_match('#^(mailto:|tel:)#i', $value)) return $value;
        $base = base_path();
        if ($base !== '' && ($value === $base || str_starts_with($value, $base . '/'))) return $value;
        return url('/' . ltrim($value, '/'));
    }
}
if (!function_exists('lighthouse_theme_media_url')) {
    function lighthouse_theme_media_url(?string $value): string {
        $value = trim((string)$value);
        if ($value === '' || preg_match('/[\x00-\x1f\x7f]/', $value) || str_contains($value, '\\')) return '';
        if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $value) && !preg_match('#^https?://#i', $value)) return '';
        return asset_url($value);
    }
}
if (!function_exists('lighthouse_theme_limit')) {
    function lighthouse_theme_limit(string $key, int $fallback = 4, int $max = 24): int {
        return max(1, min($max, (int)block($key, 'limit', (string)$fallback)));
    }
}
if (!function_exists('lighthouse_theme_visible')) {
    function lighthouse_theme_visible(string $key): bool {
        return block($key, 'is_visible', 'yes') !== 'no';
    }
}
if (!function_exists('lighthouse_theme_seo_context')) {
    function lighthouse_theme_seo_context(array $data): array {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = base_path();
        if ($base !== '' && str_starts_with($path, $base . '/')) $path = substr($path, strlen($base));
        $item = is_array($data['item'] ?? null) ? $data['item'] : [];
        $type = 'website';
        foreach (['/product/' => 'product', '/blog/' => 'article', '/case/' => 'case', '/page/' => 'page'] as $prefix => $candidate) {
            if ($item !== [] && str_starts_with($path, $prefix)) $type = $candidate;
        }
        return [
            'type' => $type, 'item' => $item, 'cover' => (string)($item['cover'] ?? ''),
            'images' => $data['images'] ?? [], 'price_mode' => $data['price_mode'] ?? ($item['price_mode'] ?? 'tier'),
            'price_tiers' => $data['price_tiers'] ?? [], 'product_skus' => $data['product_skus'] ?? [],
        ];
    }
}
if (!function_exists('lighthouse_theme_render_video')) {
    function lighthouse_theme_render_video(array $site = []): void {
        if (!lighthouse_theme_visible('home_video')) return;
        $media = lighthouse_theme_media_url(block('home_video', 'media'));
        $poster = lighthouse_theme_media_url(block('home_video', 'poster', (string)($site['company_profile_image'] ?? '')));
        ?>
        <section class="lighthouse-video" aria-labelledby="factory-video-title">
            <div class="lighthouse-video__inner">
                <div><p class="lighthouse-video__eyebrow"><?= h(block('home_video', 'eyebrow', 'INSIDE OUR FACTORY')) ?></p>
                    <h2 id="factory-video-title"><?= h(block('home_video', 'heading', 'See our production in action')) ?></h2>
                    <p><?= nl2br(h(block('home_video', 'text', 'Explore our production process and quality control.'))) ?></p>
                </div>
                <div class="lighthouse-video__media">
                    <?php if ($media !== ''): ?>
                        <video controls playsinline preload="metadata"<?= $poster !== '' ? ' poster="' . h($poster) . '"' : '' ?> aria-label="<?= h(block('home_video', 'heading')) ?>">
                            <source src="<?= h($media) ?>">
                        </video>
                    <?php elseif ($poster !== ''): ?>
                        <img src="<?= h($poster) ?>" alt="<?= h(block('home_video', 'heading')) ?>" loading="lazy" decoding="async">
                    <?php else: ?>
                        <div class="lighthouse-video__empty"><i class="fas fa-industry" aria-hidden="true"></i><p><?= h(block('home_video', 'empty_text', 'Contact our team to arrange a factory tour.')) ?></p></div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
