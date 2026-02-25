<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Setting;

/**
 * 基础网站控制器
 * 
 * 为网站前台提供统一的数据加载和渲染功能
 * 所有前台控制器（SiteController 等）均应继承此类
 * 
 * 功能：
 * - 加载网站全局设置（站点名称、SEO、公司信息等）
 * - 提供网站视图渲染方法
 * - 处理多语言支持
 */
abstract class BaseController extends Controller {
    
    /**
     * 设置模型实例
     */
    protected Setting $settingModel;
    
    /**
     * 站点数据缓存 - 包含所有全局设置信息
     * 结构：['site' => [...], 'seo' => [...], ...]
     */
    protected array $siteData = [];
    
    // 缓存的设置数据，用于减少重复查询
    private static ?array $cachedSettings = null;

    /**
     * 构造函数 - 初始化模型和加载网站数据
     */
    public function __construct() {
        $this->settingModel = new Setting();
        $this->loadSiteData();
    }

    /**
     * 加载网站全局设置数据
     * 
     * 从数据库一次加载所有设置，然后组织成易用的数据结构
     * 使用静态缓存防止重复查询同一数据库
     * 
     * @return void
     */
    protected function loadSiteData(): void {
        // 使用静态缓存减少数据库查询
        if (self::$cachedSettings === null) {
            self::$cachedSettings = $this->settingModel->getAll();
        }
        $settings = self::$cachedSettings;
        
        // 组织站点基本信息
        $this->siteData['site'] = $this->buildSiteInfo($settings);
    }

    /**
     * 构建站点基本信息数组
     * 
     * @param array $settings 原始设置数组
     * @return array 组织后的站点信息
     */
    private function buildSiteInfo(array $settings): array {
        return [
            // 基本信息
            'name' => $settings['site_name'] ?? 'B2B Company',
            'tagline' => $settings['site_tagline'] ?? '',
            'theme' => $settings['theme'] ?? 'default',
            'default_lang' => $settings['default_lang'] ?? 'en',
            'logo' => $settings['site_logo'] ?? '',
            'favicon' => $settings['site_favicon'] ?? '',
            
            // SEO 设置
            'seo_title' => $settings['seo_title'] ?? '',
            'seo_keywords' => $settings['seo_keywords'] ?? '',
            'seo_description' => $settings['seo_description'] ?? '',
            'og_image' => $settings['og_image'] ?? '',
            
            // 公司信息 - 基本
            'company_bio' => $settings['company_bio'] ?? '',
            'company_business_type' => $settings['company_business_type'] ?? '',
            'company_main_products' => $settings['company_main_products'] ?? '',
            'company_year_established' => $settings['company_year_established'] ?? '',
            'company_employees' => $settings['company_employees'] ?? '',
            'company_address' => $settings['company_address'] ?? '',
            
            // 公司信息 - 生产
            'company_plant_area' => $settings['company_plant_area'] ?? '',
            'company_registered_capital' => $settings['company_registered_capital'] ?? '',
            'company_rd_engineers' => $settings['company_rd_engineers'] ?? '',
            'company_sgs_report' => $settings['company_sgs_report'] ?? '',
            'company_rating' => $settings['company_rating'] ?? '',
            'company_response_time' => $settings['company_response_time'] ?? '',
            
            // 公司信息 - 贸易
            'company_main_markets' => $settings['company_main_markets'] ?? '',
            'company_trade_staff' => $settings['company_trade_staff'] ?? '',
            'company_incoterms' => $settings['company_incoterms'] ?? '',
            'company_payment_terms' => $settings['company_payment_terms'] ?? '',
            'company_lead_time' => $settings['company_lead_time'] ?? '',
            'company_overseas_agent' => $settings['company_overseas_agent'] ?? '',
            'company_export_year' => $settings['company_export_year'] ?? '',
            'company_nearest_port' => $settings['company_nearest_port'] ?? '',
            
            // 联系方式
            'company_email' => $settings['company_email'] ?? '',
            'company_phone' => $settings['company_phone'] ?? '',
            'whatsapp' => $settings['whatsapp'] ?? '',
            'facebook' => $settings['facebook'] ?? '',
            'instagram' => $settings['instagram'] ?? '',
            'twitter' => $settings['twitter'] ?? '',
            'linkedin' => $settings['linkedin'] ?? '',
            'youtube' => $settings['youtube'] ?? '',
            
            // 媒体和资料
            'company_show_json' => $settings['company_show_json'] ?? '[]',
            'company_certificates_json' => $settings['company_certificates_json'] ?? '[]',
            'company_profile_images_json' => $settings['company_profile_images_json'] ?? '[]',

            // 翻译设置
            'translate_enabled' => $settings['translate_enabled'] ?? '1',
            'translate_languages' => $settings['translate_languages'] ?? '[]',
            'translate_auto_browser' => $settings['translate_auto_browser'] ?? '0',

            // 自定义代码注入
            'head_code' => $settings['head_code'] ?? '',
            'footer_code' => $settings['footer_code'] ?? '',
        ];
    }

    /**
     * 渲染网站前台页面
     * 
     * @param string $view 视图文件名（如 'home'）
     * @param array $data 传递给视图的额外数据
     * @return void
     */
    protected function renderSite(string $view, array $data = []): void {
        $theme = $this->siteData['site']['theme'];
        
        // 合并网站数据
        $viewData = array_merge($this->siteData, $data);
        $viewData['lang'] = 'en';
        $viewData['languages'] = [];
        
        // 构建 SEO 元数据 - 确保 seo 键存在，避免访问不存在的键
        $viewData['seo'] = array_merge(
            $viewData['seo'] ?? [],
            [
                'title' => ($data['seo']['title'] ?? null) ?? ($this->siteData['site']['seo_title'] ?? null) ?? $this->siteData['site']['name'],
                'description' => ($data['seo']['description'] ?? null) ?? ($this->siteData['site']['seo_description'] ?? null) ?? ($this->siteData['site']['tagline'] ?? ''),
                'keywords' => ($data['seo']['keywords'] ?? null) ?? ($this->siteData['site']['seo_keywords'] ?? ''),
                'canonical' => $this->getCurrentUrl(),
            ]
        );
        
        // 调用父类的 render 方法
        $this->render($theme, $view, $viewData);
    }

    /**
     * 获取当前页面的规范 URL
     * 
     * @return string 完整的规范 URL
     */
    private function getCurrentUrl(): string {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $base = base_path();
        if ($base !== '' && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        return base_url() . ($path ?: '/');
    }
}
