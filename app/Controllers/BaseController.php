<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Setting;

abstract class BaseController extends Controller {
    protected Setting $settingModel;
    protected array $siteData = [];

    public function __construct() {
        $this->settingModel = new Setting();
        $this->loadSiteData();
    }

    protected function loadSiteData(): void {
        $settings = $this->settingModel->getAll();
        $this->siteData = [
            'site' => [
                'name' => $settings['site_name'] ?? 'B2B Company',
                'tagline' => $settings['site_tagline'] ?? '',
                'about' => $settings['company_about'] ?? '',
                'address' => $settings['company_address'] ?? '',
                'email' => $settings['company_email'] ?? '',
                'phone' => $settings['company_phone'] ?? '',
                'whatsapp' => $settings['whatsapp'] ?? '',
                'theme' => $settings['theme'] ?? 'default',
                'default_lang' => $settings['default_lang'] ?? 'en',
                'facebook' => $settings['facebook'] ?? '',
                'instagram' => $settings['instagram'] ?? '',
                'twitter' => $settings['twitter'] ?? '',
                'linkedin' => $settings['linkedin'] ?? '',
                'youtube' => $settings['youtube'] ?? '',
                'seo_title' => $settings['seo_title'] ?? '',
                'seo_keywords' => $settings['seo_keywords'] ?? '',
                'seo_description' => $settings['seo_description'] ?? '',
                'og_image' => $settings['og_image'] ?? '',
            ]
        ];
    }

    protected function renderSite(string $view, array $data = []): void {
        $theme = $this->siteData['site']['theme'];
        
        // Add multi-language helpers to data
        $lang = $_SESSION['lang'] ?? $this->siteData['site']['default_lang'];
        $data['lang'] = $lang;
        $data['languages'] = get_languages();
        $data['site'] = $this->siteData['site'];
        
        // Merge SEO
        $data['seo'] = array_merge([
            'title' => $this->siteData['site']['name'],
            'description' => $this->siteData['site']['tagline'],
            'canonical' => $this->getCurrentUrl(),
        ], $data['seo'] ?? []);

        $this->render($theme, $view, $data);
    }

    private function getCurrentUrl(): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return $scheme . '://' . $host . parse_url($uri, PHP_URL_PATH);
    }
}

