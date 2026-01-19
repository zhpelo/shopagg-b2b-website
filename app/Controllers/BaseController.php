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
        
        // Comprehensive mapping of all setting keys to the site data array
        $this->siteData = [
            'site' => [
                'name' => $settings['site_name'] ?? 'B2B Company',
                'tagline' => $settings['site_tagline'] ?? '',
                'theme' => $settings['theme'] ?? 'default',
                'default_lang' => $settings['default_lang'] ?? 'en',
                
                // SEO
                'seo_title' => $settings['seo_title'] ?? '',
                'seo_keywords' => $settings['seo_keywords'] ?? '',
                'seo_description' => $settings['seo_description'] ?? '',
                'og_image' => $settings['og_image'] ?? '',
                
                // Company Info
                'company_bio' => $settings['company_bio'] ?? '',
                'company_business_type' => $settings['company_business_type'] ?? '',
                'company_main_products' => $settings['company_main_products'] ?? '',
                'company_year_established' => $settings['company_year_established'] ?? '',
                'company_employees' => $settings['company_employees'] ?? '',
                'company_address' => $settings['company_address'] ?? '',
                'company_plant_area' => $settings['company_plant_area'] ?? '',
                'company_registered_capital' => $settings['company_registered_capital'] ?? '',
                'company_sgs_report' => $settings['company_sgs_report'] ?? '',
                'company_rating' => $settings['company_rating'] ?? '',
                'company_response_time' => $settings['company_response_time'] ?? '',
                
                // Trade Info
                'company_main_markets' => $settings['company_main_markets'] ?? '',
                'company_trade_staff' => $settings['company_trade_staff'] ?? '',
                'company_incoterms' => $settings['company_incoterms'] ?? '',
                'company_payment_terms' => $settings['company_payment_terms'] ?? '',
                'company_lead_time' => $settings['company_lead_time'] ?? '',
                'company_overseas_agent' => $settings['company_overseas_agent'] ?? '',
                'company_export_year' => $settings['company_export_year'] ?? '',
                'company_nearest_port' => $settings['company_nearest_port'] ?? '',
                'company_rd_engineers' => $settings['company_rd_engineers'] ?? '',
                
                // Contact
                'company_email' => $settings['company_email'] ?? '',
                'company_phone' => $settings['company_phone'] ?? '',
                'whatsapp' => $settings['whatsapp'] ?? '',
                'facebook' => $settings['facebook'] ?? '',
                'instagram' => $settings['instagram'] ?? '',
                'twitter' => $settings['twitter'] ?? '',
                'linkedin' => $settings['linkedin'] ?? '',
                'youtube' => $settings['youtube'] ?? '',
                
                // Media
                'company_show_json' => $settings['company_show_json'] ?? '[]',
                'company_certificates_json' => $settings['company_certificates_json'] ?? '[]',
                'company_profile_images_json' => $settings['company_profile_images_json'] ?? '[]',
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
