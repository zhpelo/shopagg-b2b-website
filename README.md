<div align="center">

# SHOPAGG B2B Website

**轻量级外贸企业 B2B 官网系统**

PHP + SQLite · 零依赖 · 开箱即用

[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1+-8892BF?logo=php&logoColor=white)](https://www.php.net/) [![SQLite](https://img.shields.io/badge/SQLite3-003B57?logo=sqlite&logoColor=white)](https://www.sqlite.org/) [![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE) [![Version](https://img.shields.io/badge/Version-1.0.0-blue.svg)](version.php)

[功能特性](#-功能特性) · [快速开始](#-快速开始) · [部署指南](#-部署指南) · [技术架构](#-技术架构) · [开发文档](#-开发文档)

</div>

---

## 🖥 界面预览

### 前台展示

<!-- 替换为实际截图 -->

| 首页 | 产品列表 | 产品详情 |
|:---:|:---:|:---:|
| ![首页](https://devtool.tech/api/placeholder/400/250?text=Homepage) | ![产品列表](https://devtool.tech/api/placeholder/400/250?text=Products) | ![产品详情](https://devtool.tech/api/placeholder/400/250?text=Product+Detail) |

| 案例展示 | 博客文章 | 联系我们 |
|:---:|:---:|:---:|
| ![案例](https://devtool.tech/api/placeholder/400/250?text=Cases) | ![博客](https://devtool.tech/api/placeholder/400/250?text=Blog) | ![联系](https://devtool.tech/api/placeholder/400/250?text=Contact) |

### 后台管理

| 仪表盘 | 产品管理 |
|:---:|:---:|
| ![仪表盘](https://devtool.tech/api/placeholder/600/350?text=Dashboard) | ![产品管理](https://devtool.tech/api/placeholder/600/350?text=Product+Management) |

| 媒体库 | 系统设置 |
|:---:|:---:|
| ![媒体库](https://devtool.tech/api/placeholder/600/350?text=Media+Library) | ![设置](https://devtool.tech/api/placeholder/600/350?text=Settings) |

---

## ✨ 功能特性

### 为什么选择 SHOPAGG？

<table>
<tr>
<td width="50%">

**🚀 极简部署**
- 无需 Composer、Node.js 或任何构建工具
- 上传文件 → 访问网址 → 自动完成初始化
- SQLite 文件数据库，无需安装数据库服务
- 支持根目录和子目录两种部署方式

</td>
<td width="50%">

**🎨 主题系统**
- 前后台完全分离的主题机制
- 区块编辑器：后台可视化修改页面文案
- 可定制品牌色、导航菜单、轮播图
- 支持自定义 head/footer 代码注入

</td>
</tr>
<tr>
<td width="50%">

**🌍 外贸场景优化**
- 产品阶梯价格与多币种支持
- 产品询盘系统（含状态跟踪、CSV 导出）
- 公司资质、贸易能力等专业展示模块
- Google 翻译集成，12 种语言一键切换

</td>
<td width="50%">

**🔒 安全可靠**
- 四层安全防护：Web 服务器 → htaccess → 响应头 → 应用层
- CSRF 令牌校验所有 POST 请求
- 文件上传 MIME 验证 + getimagesize 双重检查
- 基于角色的后台权限控制

</td>
</tr>
</table>

### 前台功能

| 模块 | 说明 |
|------|------|
| **产品中心** | 产品列表、分类筛选、详情页、图片画廊、阶梯价格、相关产品推荐 |
| **内容发布** | 博客文章、成功案例、自定义页面，统一内容引擎 |
| **企业展示** | 关于我们（公司简介、资质认证、生产能力）、联系我们 |
| **询盘系统** | 产品快捷询盘、通用联系表单，双通道获客 |
| **SEO 优化** | 全站 SEO 字段、自动生成 robots.txt 和 sitemap.xml、OG 标签 |
| **多语言** | Google Translate 集成，支持 12 种语言，可按浏览器自动翻译 |
| **轮播图** | 首页轮播图，后台可视化管理，支持自定义链接和文案 |
| **导航菜单** | 可视化拖拽排序，支持多级菜单，多个菜单区块 |

### 后台功能

| 模块 | 说明 |
|------|------|
| **仪表盘** | 核心数据概览（产品 / 文章 / 询盘 / 留言），一目了然 |
| **产品管理** | 增删改查、图片集、横幅图、标签、供应商、阶梯价格编辑 |
| **内容管理** | 案例 / 博客 / 页面统一管理，富文本编辑器 |
| **分类管理** | 产品分类和文章分类，树形结构 |
| **询盘管理** | 状态流转（待处理→已回复→已关闭）、详情查看、CSV 导出 |
| **留言管理** | 联系表单留言查看与处理 |
| **媒体库** | 文件上传、目录管理、搜索筛选、批量操作 |
| **菜单管理** | 拖拽排序、多级嵌套、多个菜单位置 |
| **轮播图管理** | 图片上传、文案编辑、链接和排序 |
| **区块编辑** | 可视化修改页面中的标题、描述、按钮文案等 |
| **员工管理** | 创建 Staff 账号，按模块分配细粒度权限 |
| **系统设置** | 基础信息 / 公司资料 / 贸易能力 / 联系方式 / 翻译 / 自定义代码 |

---

## 🚀 快速开始

### 环境要求

| 项目 | 最低要求 |
|------|---------|
| PHP | 8.1 或更高 |
| PHP 扩展 | `sqlite3`、`mbstring`、`fileinfo` |
| Web 服务器 | Apache（需 `mod_rewrite`）或 Nginx |
| 磁盘空间 | ≥ 50 MB（不含上传文件） |

### 三步完成安装

```bash
# 1. 获取项目
git clone https://github.com/zhpelo/shopagg-b2b-website.git
cd shopagg-b2b-website

# 2. 设置目录权限
chmod -R 755 storage uploads

# 3. 访问网站 —— 完成！
# 系统自动创建数据库、初始化数据表和默认管理员账号
```

### 登录后台

| 项目 | 值 |
|------|----|
| 后台地址 | `http://你的域名/admin/login` |
| 默认用户名 | `admin` |
| 默认密码 | `admin123` |

> ⚠️ **首次登录后请立即修改默认密码。**

---

## 📦 部署指南

### Apache 部署

项目已内置 `.htaccess`，确保 Apache 启用 `mod_rewrite` 和 `AllowOverride All` 即可。

**子目录部署**（如部署到 `https://example.com/b2b/`）仅需修改一行：

```apache
# .htaccess
RewriteBase /b2b/
```

系统会自动检测子目录路径，所有链接自动适配。

### Nginx 部署

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/html;
    index index.php;

    # 安全规则 —— 禁止访问敏感文件和目录
    location ~* ^/(storage|\.git)/ { deny all; }
    location ~* \.(db|sqlite|sqlite3|env|log)$ { deny all; }

    # 静态资源缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff2?)$ {
        expires 30d;
        access_log off;
    }

    # PHP 处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # URL 重写
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### 生产环境清单

- [ ] 修改默认管理员密码
- [ ] 启用 HTTPS
- [ ] 确认 `storage/` 和 `uploads/` 对 Web 用户可写
- [ ] 确认 `storage/` 目录禁止 Web 直接访问
- [ ] 关闭调试模式（删除 `.env` 或设为 `APP_DEBUG=false`）
- [ ] 定期备份 `storage/site.db` 和 `uploads/`
- [ ] 为 Staff 账号分配最小必要权限

---

## 🏗 技术架构

### 技术栈

| 层次 | 技术 | 说明 |
|------|------|------|
| **后端** | PHP 8.1+ | 纯 PHP，无框架依赖 |
| **数据库** | SQLite3 | 文件数据库，WAL 模式 |
| **架构** | MVC | PSR-4 自动加载 |
| **前端 CSS** | Tailwind CSS | CDN JIT 模式，无需构建 |
| **图标** | Font Awesome 6 | CDN 引入 |
| **富文本** | Jodit Editor | 后台内容编辑器 |
| **轮播** | Swiper.js 11 | 按需加载 |
| **拖拽排序** | SortableJS | 菜单排序 |

### 系统架构图

```
                            ┌─────────────┐
                            │  HTTP 请求   │
                            └──────┬──────┘
                                   │
                                   ▼
                      ┌────────────────────────┐
                      │   index.php（单入口）    │
                      │  常量 · Session · 加载   │
                      └────────────┬───────────┘
                                   │
                    ┌──────────────┼──────────────┐
                    ▼              ▼               ▼
             ┌────────────┐ ┌──────────┐  ┌─────────────┐
             │   Router   │ │ Helpers  │  │  Database   │
             │  路由分发   │ │ 全局函数  │  │ SQLite 单例  │
             └─────┬──────┘ └──────────┘  └──────┬──────┘
                   │                             │
          ┌────────┴────────┐                    │
          ▼                 ▼                    ▼
   ┌──────────────┐  ┌───────────────┐   ┌───────────┐
   │  Site        │  │  Admin        │   │  Migrator  │
   │  Controller  │  │  Controller   │   │  迁移系统   │
   │  (前台)       │  │  (后台)       │   └───────────┘
   └──────┬───────┘  └───────┬───────┘
          │                  │
          ▼                  ▼
   ┌──────────────┐  ┌───────────────┐
   │   Models     │  │    Views      │
   │  数据模型     │  │ 主题 / 后台    │
   └──────────────┘  └───────────────┘
```

### 请求生命周期

```
浏览器请求 GET /product/steel-pipe
    │
    ├── 1. Apache/Nginx 重写 → index.php
    ├── 2. 定义常量、Session、错误处理
    ├── 3. PSR-4 自动加载 + 全局辅助函数
    ├── 4. Database::getInstance() → 自动执行迁移
    ├── 5. Router 匹配 → SiteController::productDetail('steel-pipe')
    ├── 6. 控制器查询 Model → 组装数据
    └── 7. 渲染: header.php + product_detail.php + footer.php → HTML 输出
```

### 目录结构

```
├── index.php                   # 唯一入口
├── .htaccess                   # Apache 重写 + 安全规则
├── .env                        # 环境变量（APP_DEBUG）
│
├── app/                        # 应用核心
│   ├── Core/                   # 框架核心（Router / Database / Auth / Media / Migrator）
│   ├── Controllers/            # 控制器（Site / Admin / Base）
│   ├── Models/                 # 数据模型（11 个）
│   ├── Helpers/                # 全局辅助函数 + 安全工具
│   ├── Migrations/             # 版本化数据库迁移（12 个）
│   ├── views/admin/            # 后台视图模板
│   └── routes.php              # 路由注册
│
├── themes/                     # 前台主题
│   └── default/                # 默认主题（18 个模板 + blocks 定义）
│
├── storage/                    # 运行时数据（禁止 Web 访问）
│   ├── site.db                 # SQLite 数据库
│   ├── blocks/                 # 区块自定义配置
│   ├── backups/                # 数据库备份
│   └── logs/                   # 更新日志
│
├── uploads/                    # 用户上传文件（按月分目录）
├── assets/admin/               # 后台静态资源
└── Documents/                  # 开发文档
```

### 数据模型

系统使用 12 张数据表：

| 表 | 说明 | 关键字段 |
|---|------|---------|
| `users` | 后台用户 | `role`(admin/staff)、`permissions` |
| `settings` | 站点配置 | key-value 存储，38 条默认配置 |
| `products` | 产品 | `slug`、`images_json`、`category_id` |
| `product_prices` | 阶梯价格 | `min_qty`、`max_qty`、`price`、`currency` |
| `product_categories` | 统一分类 | `type`(product/post)、`parent_id` 树形 |
| `posts` | 文章/案例/页面 | `post_type`(post/case/page) |
| `inquiries` | 产品询盘 | `status`(pending/replied/closed) |
| `messages` | 联系留言 | — |
| `sliders` | 轮播图区块 | `slug` 标识 |
| `slider_items` | 轮播图片 | `slider_id` 外键、`sort_order` |
| `menus` | 菜单区块 | `slug` 标识（main-nav / footer） |
| `menu_items` | 菜单项 | `parent_id` 无限级、`sort_order` |
| `media_files` | 媒体文件索引 | 路径、MIME、尺寸等元数据 |

> 💡 博客、案例、自定义页面共用 `posts` 表，通过 `post_type` 区分。新增内容类型时优先复用此表。

---

## 📖 开发文档

完整的开发文档位于 `Documents/` 目录：

| 文档 | 说明 |
|------|------|
| `系统架构文档.md` | 分层架构、核心模块详解、数据库设计、安全架构 |
| `网站模板开发指南.md` | 主题开发规范、模板变量参考、辅助函数 API、区块系统 |

### 快速扩展

**新增前台页面：**

```php
// 1. app/routes.php — 注册路由
$router->add('GET', '/faq', [SiteController::class, 'faq']);

// 2. SiteController — 添加方法
public function faq(): void {
    $this->renderSite('faq', ['seo' => ['title' => 'FAQ']]);
}

// 3. themes/default/faq.php — 创建模板
```

**新增数据库表：**

```php
// 创建迁移文件: app/Migrations/20260412120000_create_faqs_table.php
return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS faqs (...)');
    }
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS faqs');
    }
};
// 下次请求时自动执行，无需手动操作
```

---

## 🤝 贡献

欢迎提交 Issue 和 Pull Request。

1. Fork 本仓库
2. 创建功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交更改 (`git commit -m 'Add amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 提交 Pull Request

---

## 📄 License

[MIT License](LICENSE) — 可自由使用、修改和分发。
