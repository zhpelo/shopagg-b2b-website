<div align="center">

# Lighthouse CMS

**An open-source B2B website system for Chinese manufacturers and trading companies**

PHP + SQLite · No package-manager dependencies · Ready to use

[简体中文](README.md) · [English](README.en.md)

[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1+-8892BF?logo=php&logoColor=white)](https://www.php.net/) [![SQLite](https://img.shields.io/badge/SQLite3-003B57?logo=sqlite&logoColor=white)](https://www.sqlite.org/) [![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE) ![Version](https://img.shields.io/badge/Version-1.3.0-blue.svg)

[Features](#-features) · [Quick start](#-quick-start) · [Deployment](#-deployment) · [Architecture](#-architecture) · [Developer documentation](#-developer-documentation)

</div>

---

## 📣 Brand announcement

The project formerly known as **SHOPAGG B2B Website** is now named **Lighthouse CMS** (**灯塔 CMS** in Chinese). Open-sourced by SHOPAGG, Lighthouse CMS helps Chinese manufacturers and trading companies build and manage their own B2B export websites.

This is a project-brand change. The existing GitHub repository and demo URLs, the `shopagg` command-line tool, and the ShopAgg plugin marketplace retain their current identifiers. References to those identifiers below point to the actual URLs, command, and service in use.

---

## 🖥 Preview

Live frontend demo: https://demo.shopagg.org/

### Frontend

<img alt="Frontend preview" src="https://github.com/user-attachments/assets/2f9f289a-3ccf-48b5-a959-aaa3699cc9ce" />

### Admin panel

| Dashboard | Product management |
|:---:|:---:|
| <img alt="Dashboard preview" src="https://github.com/user-attachments/assets/b6400b0b-f345-4321-9c90-5925c1e6b790" /> | <img alt="Product management preview" src="https://github.com/user-attachments/assets/86918ca6-dd59-4fde-bacf-eef4dc668460" /> |

| Media library | System settings |
|:---:|:---:|
| <img alt="Media library preview" src="https://github.com/user-attachments/assets/18a252ad-de48-4a3f-8c6b-8fee5833cc6e" /> | <img alt="System settings preview" src="https://github.com/user-attachments/assets/21f24c46-7716-47df-bdeb-0cf2228ba5fd" /> |

---

## ✨ Features

### Why Lighthouse CMS?

<table>
<tr>
<td width="50%">

**🚀 Simple deployment**
- No Composer, Node.js, or build tools required
- Upload the files, visit the site, and let it initialize automatically
- SQLite database file; no separate database server required
- Supports installation at the domain root or in a subdirectory

</td>
<td width="50%">

**🎨 Theme system**
- Separate frontend and admin theme mechanisms
- Visual block editor for page copy, product selection, and sliders
- Customizable brand colors, navigation, and homepage sliders
- Mobile-first default theme for product browsing and inquiries
- Custom code injection in the page head and footer

</td>
</tr>
<tr>
<td width="50%">

**🌍 Built for B2B exporting**
- Tiered pricing, SKU pricing, price ranges, and prices on request, with a global currency setting
- Product inquiries with status tracking and CSV export
- Modules for certifications and trading capabilities
- Google Translate integration with a 12-language quick switcher

</td>
<td width="50%">

**🔒 Security controls**
- Four layers of protection: web server, `.htaccess`, response headers, and application code
- CSRF tokens on all POST requests
- MIME and `getimagesize` checks for file uploads
- Role-based admin permissions

</td>
</tr>
</table>

### Plugin system (v1.3)

- An in-process, modular plugin runtime compatible with standard shared PHP hosting.
- Plugins can register frontend and admin pages, APIs, webhooks, events, filters, page slots, business services, and chunked tasks.
- Manifests compile to a PHP cache when plugins are enabled or disabled; normal requests do not scan plugin directories.
- Plugins have namespaced tables, migrations, settings, logs, tasks, and dynamic Staff permissions.
- Supports ZIP upload in the admin panel, the ShopAgg plugin marketplace, version upgrades, and uninstallation that preserves data by default.
- Includes an AI specification, JSON Schema, CLI tools to generate, validate, and package plugins, and a working example.

```bash
php shopagg plugin:make my-plugin
php shopagg plugin:validate plugin-dev/my-plugin
php shopagg plugin:pack plugin-dev/my-plugin
```

See the [plugin development guide](../Documents/shopagg-b2b-website/Documents/插件开发指南.md). You can also provide the [AI plugin specification](../Documents/shopagg-b2b-website/Documents/AI_PLUGIN_SPEC.md) as context when generating a plugin. The machine-readable schema remains in this repository at `Documents/plugin.schema.json`.

### Frontend

| Module | Description |
|---|---|
| **Product catalog** | Listings, category filters, detail pages, galleries, tiered and SKU pricing, price ranges, prices on request, and related products |
| **Content publishing** | Blog posts, case studies, and custom pages in one content engine |
| **Company profile** | About and contact pages, certifications, and production capabilities |
| **Inquiries** | Product-specific inquiry forms and a general contact form |
| **SEO** | Site-wide SEO fields, generated `robots.txt` and `sitemap.xml`, and Open Graph tags |
| **Languages** | Google Translate integration supporting 249 languages and optional automatic translation based on the browser language |
| **Sliders** | Visually managed homepage sliders with custom links and text |
| **Navigation** | Drag-and-drop ordering, nested items, and multiple menu areas |

### Admin panel

| Module | Description |
|---|---|
| **Dashboard** | Summary of products, posts, inquiries, and messages |
| **Products** | Create, edit, and delete products; galleries, banners, tags, suppliers, tiered and SKU pricing, price ranges, and bulk editing |
| **Content** | Manage case studies, blog posts, and pages with a rich-text editor |
| **Categories** | Hierarchical product and post categories |
| **Inquiries** | Pending → replied → closed workflow, details, and CSV export |
| **Messages** | View and handle contact-form messages |
| **Media library** | Uploads, folders, search, filters, and bulk actions |
| **Menus** | Drag-and-drop ordering, nested items, and multiple menu locations |
| **Sliders** | Image upload, copy editing, links, and ordering |
| **Block editor** | Visual editing for page titles, descriptions, buttons, and more |
| **Staff** | Staff accounts with module-level permissions |
| **Settings** | Site details, global currency, company profile, trading capabilities, contact details, translation, and custom code |

---

## 🚀 Quick start

### Requirements

| Item | Minimum |
|---|---|
| PHP | 8.1 or newer |
| PHP extensions | `sqlite3`, `mbstring`, `fileinfo` |
| Web server | Apache with `mod_rewrite`, or Nginx |
| Disk space | At least 50 MB, excluding uploads |

### Install in three steps

```bash
# 1. Get the project
git clone https://github.com/zhpelo/shopagg-b2b-website.git
cd shopagg-b2b-website

# 2. Set directory permissions
chmod -R 755 storage uploads

# 3. Visit the site to finish installation
# The system creates the database, tables, and initial admin account automatically.
```

### Admin login

| Item | Value |
|---|---|
| URL | `http://your-domain/admin/login` |
| Default username | `admin` |
| Default password | `admin123` |

> ⚠️ **Change the default password immediately after your first login.**

---

## 📦 Deployment

### Apache

The repository includes `.htaccess`. Enable `mod_rewrite` and `AllowOverride All` in Apache.

For a **subdirectory installation** such as `https://example.com/b2b/`, change one line:

```apache
# .htaccess
RewriteBase /b2b/
```

The system detects the subdirectory and adjusts its links automatically.

### Nginx

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/html;
    index index.php;

    # Deny access to sensitive files and directories
    location ~* ^/(storage|\.git)/ { deny all; }
    location ~* \.(db|sqlite|sqlite3|env|log)$ { deny all; }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff2?)$ {
        expires 30d;
        access_log off;
    }

    # Handle PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Rewrite URLs
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### Production checklist

- [ ] Change the default admin password.
- [ ] Enable HTTPS.
- [ ] Make `storage/` and `uploads/` writable by the web-server user.
- [ ] Deny direct web access to `storage/`.
- [ ] Disable debugging by removing `.env` or setting `APP_DEBUG=false`.
- [ ] Back up `storage/site.db` and `uploads/` regularly.
- [ ] Grant Staff accounts only the permissions they need.

---

## 🏗 Architecture

### Technology stack

| Layer | Technology | Notes |
|---|---|---|
| **Backend** | PHP 8.1+ | Plain PHP; no framework dependency |
| **Database** | SQLite3 | File-based database in WAL mode |
| **Architecture** | MVC | PSR-4 autoloading |
| **Frontend CSS** | Tailwind CSS | CDN JIT mode; no build step |
| **Icons** | Font Awesome 6 | Loaded from a CDN |
| **Rich text** | Jodit Editor | Admin content editor |
| **Sliders** | Swiper.js 11 | Loaded when needed |
| **Drag-and-drop** | SortableJS | Menu sorting |

### System diagram

```text
HTTP request
    │
    ▼
index.php (single entry point: constants, session, loading)
    │
    ├── Router ──► SiteController  ──► Models ──► Frontend theme
    │          └─► AdminController ──► Models ──► Admin views
    ├── Helpers
    └── Database (SQLite singleton) ──► Migrator
```

### Request lifecycle

```text
Browser requests GET /product/steel-pipe
    │
    ├── 1. Apache/Nginx rewrites the URL to index.php
    ├── 2. Initialize constants, session, and error handling
    ├── 3. Load PSR-4 classes and global helpers
    ├── 4. Database::getInstance() runs pending migrations
    ├── 5. Router calls SiteController::productDetail('steel-pipe')
    ├── 6. Controller queries models and prepares data
    └── 7. Render header.php + product_detail.php + footer.php as HTML
```

### Directory layout

```text
├── index.php                   # Single entry point
├── .htaccess                   # Apache rewrite and security rules
├── .env                        # Environment variables, including APP_DEBUG
│
├── app/                        # Application core
│   ├── Core/                   # Router, Database, Auth, Media, Migrator
│   ├── Controllers/            # Site, Admin, and Base controllers
│   ├── Models/                 # Data models (12)
│   ├── Helpers/                # Global helpers and security utilities
│   ├── Migrations/             # Versioned database migrations (17)
│   ├── views/admin/            # Admin view templates
│   └── routes.php              # Route registration
│
├── themes/                     # Frontend themes
│   └── default/                # Responsive default theme and block definitions
│
├── storage/                    # Runtime data; deny web access
│   ├── site.db                 # SQLite database
│   ├── blocks/                 # Custom block configuration
│   ├── backups/                # Database backups
│   └── logs/                   # Update logs
│
├── uploads/                    # User uploads, organized by month
├── assets/admin/               # Admin assets
└── Documents/plugin.schema.json # Machine-readable plugin schema; reading docs are in the workspace Documents/
```

### Data model

The system currently has 17 tables:

| Table | Purpose | Key fields |
|---|---|---|
| `users` | Admin users | `role` (admin/staff), `permissions` |
| `settings` | Site settings | Key-value data, including global `site_currency` |
| `products` | Products | `slug`, `images_json`, `category_id`, `price_mode`, `price_range_min`, `price_range_max` |
| `product_prices` | Tiered prices | `min_qty`, `max_qty`, `price`; `currency` is a legacy field |
| `product_skus` | SKU prices | `sku_name`, `min_qty`, `price`, `sort_order` |
| `product_categories` | Shared categories | `type` (product/post), hierarchical `parent_id` |
| `posts` | Blog posts, cases, pages | `post_type` (post/case/page) |
| `inquiries` | Product inquiries | `status` (pending/replied/closed) |
| `messages` | Contact messages | — |
| `sliders` | Slider blocks | `slug` |
| `slider_items` | Slider images | `slider_id`, `sort_order` |
| `menus` | Menu blocks | `slug` (main-nav/footer) |
| `menu_items` | Menu items | Hierarchical `parent_id`, `sort_order` |
| `media_files` | Media index | Path, MIME type, dimensions, and other metadata |
| `update_logs` | System update logs | Version, status, execution time |
| `app_store_theme_installs` | App Store theme installs | Theme asset, license, and installation state |
| `migrations` | Migration history | Applied migration files |

> 💡 Blog posts, case studies, and custom pages share the `posts` table and are distinguished by `post_type`. Product prices use `settings.site_currency`; the legacy `currency` field on price rows is no longer read.

---

## 📖 Developer documentation

The complete developer documentation is centralized in the workspace [Documents directory](../Documents/shopagg-b2b-website/Documents/):

| Document | Contents |
|---|---|
| [System architecture](../Documents/shopagg-b2b-website/Documents/系统架构文档.md) | Layers, core modules, database design, and security architecture (Chinese) |
| [Theme development guide](../Documents/shopagg-b2b-website/Documents/网站模板开发指南.md) | Theme conventions, template variables, helpers, and blocks (Chinese) |
| [Plugin development guide](../Documents/shopagg-b2b-website/Documents/插件开发指南.md) | Plugin development (Chinese) |
| [AI plugin specification](../Documents/shopagg-b2b-website/Documents/AI_PLUGIN_SPEC.md) | Plugin specification for AI-assisted development |

### Theme development conventions

- Put customer-editable content in `themes/{theme}/blocks.php`; read it in frontend templates with `block()` and `block_all()`.
- Use `product_picker` for product blocks instead of asking admins to enter product IDs.
- Manage homepage hero and banner slides at `/admin/appearance/sliders`; select slider blocks with a `slider_slug` field.
- Use `image` or `media` fields for assets, then render them with `asset_url()` and escape output with `h()`.
- Prefer Tailwind CSS classes when changing new or default themes. Keep `style.css` for theme metadata and a small amount of additional CSS.
- Before submitting a theme change, check PHP syntax, confirm `product_picker` and `slider_slug` work in `blocks.php`, and verify there is no horizontal overflow on mobile.

### Extend the system

**Add a frontend page:**

```php
// 1. Register a route in app/routes.php
$router->add('GET', '/faq', [SiteController::class, 'faq']);

// 2. Add a method to SiteController
public function faq(): void {
    $this->renderSite('faq', ['seo' => ['title' => 'FAQ']]);
}

// 3. Create themes/default/faq.php
```

**Add a database table:**

```php
// Create app/Migrations/20260412120000_create_faqs_table.php
return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS faqs (...)');
    }
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS faqs');
    }
};
// Runs automatically on the next request.
```

---

## 🤝 Contributing

Issues and pull requests are welcome.

1. Fork this repository.
2. Create a feature branch (`git checkout -b feature/amazing-feature`).
3. Commit your changes (`git commit -m 'Add amazing feature'`).
4. Push the branch (`git push origin feature/amazing-feature`).
5. Open a pull request.

---

## 📄 License

[MIT License](LICENSE) — free to use, modify, and distribute.
