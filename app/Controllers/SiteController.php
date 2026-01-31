<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\CaseModel;
use App\Models\PostModel;
use App\Models\Message;
use App\Models\Inquiry;

class SiteController extends BaseController {
    public function home(): void {
        $products = (new Product())->getList(6, true);  // 只获取已上架产品
        foreach ($products as &$p) $p['url'] = url('/product/' . $p['slug']);
        
        $cases = (new CaseModel())->getList(6);
        foreach ($cases as &$c) $c['url'] = url('/case/' . $c['slug']);
        
        // 首页使用全局 SEO 设置
        $this->renderSite('home', [
            'products' => $products,
            'cases' => $cases,
            'seo' => [
                'title' => $this->siteData['site']['seo_title'] ?: $this->siteData['site']['name'],
                'description' => $this->siteData['site']['seo_description'] ?: $this->siteData['site']['tagline'],
                'keywords' => $this->siteData['site']['seo_keywords'] ?? '',
            ]
        ]);
    }

    public function products(): void {
        $categoryModel = new Category();
        $productModel = new Product();
        
        // 获取当前分类ID
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
        $currentCategory = $categoryId > 0 ? $categoryModel->getById($categoryId) : null;
        
        // 获取产品列表
        if ($categoryId > 0) {
            $items = $productModel->getByCategory($categoryId);
        } else {
            $items = $productModel->getList(0, true);  // 只获取已上架产品
        }
        foreach ($items as &$i) $i['url'] = url('/product/' . $i['slug']);
        
        // 获取分类列表
        $categories = $categoryModel->getTree('product');
        
        $title = $currentCategory ? $currentCategory['name'] : t('products');
        
        $this->renderSite('product_list', [
            'title' => $title,
            'items' => $items,
            'categories' => $categories,
            'current_category' => $currentCategory,
            'seo' => ['title' => $title . ' - ' . $this->siteData['site']['name']]
        ]);
    }

    public function productDetail(string $slug): void {
        $productModel = new Product();
        $categoryModel = new Category();
        
        $item = $productModel->getBySlug($slug);
        // 只有已上架的产品才能查看
        if (!$item || $item['status'] !== 'active') { $this->notFound(); return; }
        
        // 获取分类信息用于面包屑
        $category = null;
        if (!empty($item['category_id'])) {
            $category = $categoryModel->getById((int)$item['category_id']);
        }
        
        $this->renderSite('product_detail', [
            'item' => $item,
            'category' => $category,
            'images' => $item['images'] ?? [],
            'price_tiers' => $productModel->getPrices((int)$item['id']),
            'whatsapp' => $this->siteData['site']['whatsapp'],
            'inquiry_form' => true,
            'seo' => [
                'title' => ($item['seo_title'] ?: $item['title']) . ' - ' . $this->siteData['site']['name'],
                'description' => $item['seo_description'] ?: ($item['summary'] ?: $this->siteData['site']['tagline']),
                'keywords' => $item['seo_keywords'] ?? '',
            ]
        ]);
    }

    public function cases(): void {
        $items = (new CaseModel())->getList();
        foreach ($items as &$i) $i['url'] = url('/case/' . $i['slug']);
        $this->renderSite('case_list', [
            'title' => t('cases'),
            'items' => $items,
            'seo' => ['title' => t('cases') . ' - ' . $this->siteData['site']['name']]
        ]);
    }

    public function caseDetail(string $slug): void {
        $item = (new CaseModel())->getBySlug($slug);
        if (!$item) { $this->notFound(); return; }
        $this->renderSite('case_detail', [
            'item' => $item,
            'seo' => [
                'title' => ($item['seo_title'] ?: $item['title']) . ' - ' . $this->siteData['site']['name'],
                'description' => $item['seo_description'] ?: ($item['summary'] ?? ''),
                'keywords' => $item['seo_keywords'] ?? '',
            ]
        ]);
    }

    public function blog(): void {
        $categoryModel = new Category();
        $postModel = new PostModel();
        
        // 获取当前分类ID
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
        $currentCategory = $categoryId > 0 ? $categoryModel->getById($categoryId) : null;
        
        // 获取文章列表
        if ($categoryId > 0) {
            $items = $postModel->getByCategory($categoryId);
        } else {
            $items = $postModel->getList(0, true);  // 只获取已发布文章
        }
        foreach ($items as &$i) $i['url'] = url('/blog/' . $i['slug']);
        
        // 获取分类列表（带文章数量）
        $categories = $categoryModel->getTree('post');
        
        $title = $currentCategory ? $currentCategory['name'] : t('blog');
        
        $this->renderSite('post_list', [
            'title' => $title,
            'items' => $items,
            'categories' => $categories,
            'current_category' => $currentCategory,
            'seo' => ['title' => $title . ' - ' . $this->siteData['site']['name']]
        ]);
    }

    public function blogDetail(string $slug): void {
        $postModel = new PostModel();
        $categoryModel = new Category();
        
        $item = $postModel->getBySlug($slug);
        // 只有已发布的文章才能查看
        if (!$item || ($item['status'] ?? 'active') !== 'active') { $this->notFound(); return; }
        
        // 获取分类信息用于面包屑
        $category = null;
        if (!empty($item['category_id'])) {
            $category = $categoryModel->getById((int)$item['category_id']);
        }
        
        $this->renderSite('post_detail', [
            'item' => $item,
            'category' => $category,
            'seo' => [
                'title' => ($item['seo_title'] ?: $item['title']) . ' - ' . $this->siteData['site']['name'],
                'description' => $item['seo_description'] ?: ($item['summary'] ?? ''),
                'keywords' => $item['seo_keywords'] ?? '',
            ]
        ]);
    }

    public function about(): void {
        $this->renderSite('about', [
            'seo' => ['title' => 'About Us - ' . $this->siteData['site']['name']]
        ]);
    }

    public function contact(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            (new Message())->create([
                'name' => trim((string)$_POST['name']),
                'email' => trim((string)$_POST['email']),
                'company' => trim((string)($_POST['company'] ?? '')),
                'phone' => trim((string)($_POST['phone'] ?? '')),
                'message' => trim((string)$_POST['message']),
            ]);
            $this->renderSite('thanks');
            return;
        }
        $this->renderSite('contact', ['seo' => ['title' => 'Contact - ' . $this->siteData['site']['name']]]);
    }

    public function inquiry(): void {
        csrf_check();
        (new Inquiry())->create([
            'product_id' => (int)($_POST['product_id'] ?? 0),
            'name' => trim((string)$_POST['name']),
            'email' => trim((string)$_POST['email']),
            'company' => trim((string)($_POST['company'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'quantity' => trim((string)($_POST['quantity'] ?? '')),
            'message' => trim((string)($_POST['message'] ?? '')),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'source_url' => $_SERVER['HTTP_REFERER'] ?? '',
        ]);
        $this->renderSite('thanks');
    }

    public function robots(): void {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\nAllow: /\nSitemap: " . base_url() . "/sitemap.xml\n";
        exit;
    }

    public function sitemap(): void {
        header('Content-Type: application/xml; charset=utf-8');
        $db = \App\Core\Database::getInstance();
        $urls = [base_url() . '/', base_url() . '/products', base_url() . '/cases', base_url() . '/blog', base_url() . '/contact'];
        
        $res = $db->query("SELECT slug FROM products WHERE status = 'active'");  // 只索引已上架产品
        while ($r = $res->fetchArray(SQLITE3_ASSOC)) $urls[] = base_url() . '/product/' . $r['slug'];
        $res = $db->query("SELECT slug FROM cases");
        while ($r = $res->fetchArray(SQLITE3_ASSOC)) $urls[] = base_url() . '/case/' . $r['slug'];
        $res = $db->query("SELECT slug FROM posts WHERE status = 'active'");  // 只索引已发布文章
        while ($r = $res->fetchArray(SQLITE3_ASSOC)) $urls[] = base_url() . '/blog/' . $r['slug'];

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $u) echo "  <url><loc>" . h($u) . "</loc></url>\n";
        echo "</urlset>";
        exit;
    }

    public function notFound(): void {
        http_response_code(404);
        $this->renderSite('404', ['seo' => ['title' => '404 - ' . $this->siteData['site']['name']]]);
    }
}
