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
        $products = (new Product())->getList(6);
        foreach ($products as &$p) $p['url'] = '/product/' . $p['slug'];
        
        $cases = (new CaseModel())->getList(6);
        foreach ($cases as &$c) $c['url'] = '/case/' . $c['slug'];
        
        $this->renderSite('home', [
            'products' => $products,
            'cases' => $cases,
            'seo' => [
                'title' => $this->siteData['site']['name'],
                'description' => $this->siteData['site']['tagline'],
            ]
        ]);
    }

    public function products(): void {
        $items = (new Product())->getList();
        foreach ($items as &$i) $i['url'] = '/product/' . $i['slug'];
        $this->renderSite('list', [
            'title' => t('products'),
            'items' => $items,
            'show_category' => true,
            'show_image' => true,
            'seo' => ['title' => t('products') . ' - ' . $this->siteData['site']['name']]
        ]);
    }

    public function productDetail(string $slug): void {
        $productModel = new Product();
        $item = $productModel->getBySlug($slug);
        if (!$item) { $this->notFound(); return; }
        
        $this->renderSite('detail', [
            'item' => $item,
            'images' => $item['images'] ?? [],
            'price_tiers' => $productModel->getPrices((int)$item['id']),
            'whatsapp' => $this->siteData['site']['whatsapp'],
            'inquiry_form' => true,
            'seo' => [
                'title' => $item['title'] . ' - ' . $this->siteData['site']['name'],
                'description' => $item['summary'] ?: $this->siteData['site']['tagline'],
            ]
        ]);
    }

    public function cases(): void {
        $items = (new CaseModel())->getList();
        foreach ($items as &$i) $i['url'] = '/case/' . $i['slug'];
        $this->renderSite('list', [
            'title' => t('cases'),
            'items' => $items,
            'seo' => ['title' => t('cases') . ' - ' . $this->siteData['site']['name']]
        ]);
    }

    public function caseDetail(string $slug): void {
        $item = (new CaseModel())->getBySlug($slug);
        if (!$item) { $this->notFound(); return; }
        $this->renderSite('detail', [
            'item' => $item,
            'seo' => ['title' => $item['title'] . ' - ' . $this->siteData['site']['name']]
        ]);
    }

    public function blog(): void {
        $items = (new PostModel())->getList();
        foreach ($items as &$i) $i['url'] = '/blog/' . $i['slug'];
        $this->renderSite('list', [
            'title' => t('blog'),
            'items' => $items,
            'seo' => ['title' => t('blog') . ' - ' . $this->siteData['site']['name']]
        ]);
    }

    public function blogDetail(string $slug): void {
        $item = (new PostModel())->getBySlug($slug);
        if (!$item) { $this->notFound(); return; }
        $this->renderSite('detail', [
            'item' => $item,
            'seo' => ['title' => $item['title'] . ' - ' . $this->siteData['site']['name']]
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
            'message' => trim((string)($_POST['message'] ?? '')),
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
        
        $res = $db->query("SELECT slug FROM products");
        while ($r = $res->fetchArray(SQLITE3_ASSOC)) $urls[] = base_url() . '/product/' . $r['slug'];
        $res = $db->query("SELECT slug FROM cases");
        while ($r = $res->fetchArray(SQLITE3_ASSOC)) $urls[] = base_url() . '/case/' . $r['slug'];
        $res = $db->query("SELECT slug FROM posts");
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
