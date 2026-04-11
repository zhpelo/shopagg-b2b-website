# SHOPAGG B2B Website

一个面向外贸企业官网场景的轻量级 B2B CMS。项目基于 PHP + SQLite3，采用简单 MVC 结构，不依赖 Composer 或前端构建工具，首次访问即可自动初始化数据库和默认管理员账号。

当前代码的核心特点是：单入口、自动建库、前后台分离、支持二级目录部署、支持产品/案例/博客/自定义页面、内置媒体库和员工权限管理。

## 项目概览

- **运行环境简单**：PHP 8.0+、SQLite3、mbstring 即可启动
- **零依赖部署**：无 Composer、无 Node.js、无额外服务
- **单入口路由**：所有请求统一由 `index.php` 进入
- **自动初始化**：首次访问自动创建 `#data/site.db` 并写入基础表结构
- **子目录部署友好**：自动根据 `SCRIPT_NAME` 计算 `APP_BASE_PATH`
- **主题机制简单**：前台模板位于 `themes`，后台模板位于 `app/views/admin`

## 已实现功能

### 前台能力

- 首页聚合展示产品和案例
- 产品列表、产品详情、分类筛选、阶梯价格展示
- 案例列表、案例详情
- 博客列表、博客详情、文章分类
- 自定义页面详情页，路由格式为 `/page/:slug`
- 关于我们、联系我们、留言提交、产品询盘提交
- 自动生成 `robots.txt` 和 `sitemap.xml`
- SEO 字段支持标题、关键词、描述、OG 图片
- 支持主题切换与自定义 head/footer 代码注入
- 支持基础语言配置和 Google Translate 小组件开关

### 后台能力

- 管理员登录、登出、个人资料维护
- 仪表盘统计：产品、文章、页面、留言、询盘、员工数量和最近趋势
- 产品管理：增删改查、图片集、横幅图、标签、供应商、SEO、阶梯价格
- 分类管理：产品分类和文章分类共用一套树形分类模型
- 内容管理：案例、博客、自定义页面统一存储在 `posts` 表，用 `post_type` 区分
- 留言管理：留言列表、详情、删除
- 询盘管理：状态流转、详情查看、CSV 导出
- 媒体库管理：文件上传、文件夹创建删除、目录浏览、搜索、筛选、删除
- 员工管理：管理员可创建 staff 账号并按权限分配后台模块访问范围
- 系统设置：基础信息、公司信息、贸易能力、媒体资料、联系方式、翻译、自定义代码

### 系统特性

- SQLite 自动补列和兼容迁移
- 旧版 cases 表内容会迁移到 `posts.post_type = case`
- POST 请求带 CSRF 校验
- 后台基于 session 的登录态和角色/权限控制
- 图片和媒体文件写入 `uploads`，并同步索引到 `media_files` 表
- 路由支持 `index.php?r=/path` 形式作为兼容入口

## 技术栈

| 分类 | 实际实现 |
|------|----------|
| 后端语言 | PHP 8.0+ |
| 数据库 | SQLite3 |
| 架构 | 轻量 MVC + PSR-4 风格自动加载 |
| 路由 | 自定义 Router |
| 前台 UI | Tailwind CSS CDN + 自定义样式 |
| 后台 UI | Tailwind CSS CDN + 自定义样式 |
| 富文本编辑器 | Jodit |
| 图标 | Font Awesome |
| 轮播 | Swiper |
| 文件存储 | 本地 uploads 目录 |

## 环境要求

| 项目 | 要求 |
|------|------|
| PHP | 8.0 或更高版本 |
| 扩展 | SQLite3、mbstring |
| Web 服务器 | Apache 或 Nginx |
| 目录权限 | `#data`、`uploads` 需要可写 |

## 快速部署

### 1. 获取项目

```bash
git clone https://github.com/zhpelo/shopagg-b2b-website.git
```

也可以直接上传整个项目目录到服务器。

### 2. 设置目录权限

```bash
chmod -R 755 #data
chmod -R 755 uploads
```

如果 PHP-FPM 或 Apache 用户无写权限，请按实际运行用户调整属主或权限。

### 3. 配置 Web 根目录

将站点根目录指向项目根目录，也就是包含 `index.php` 的目录。

### 4. 配置 URL 重写

#### Apache

项目根目录已经包含 `.htaccess`，确保启用 `mod_rewrite`。

根目录部署时保持默认配置即可：

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /

  # 禁止直接访问 #data 目录及其内容
  RewriteRule ^#data/ - [F,L]
  
  # 禁止访问敏感文件类型
  RewriteRule \.(db|sqlite|sqlite3|env|log|ini|sh|bash)$ - [F,L]
  
  # 禁止访问版本控制目录
  RewriteRule ^\.git/ - [F,L]
  RewriteRule ^\.svn/ - [F,L]
  RewriteRule ^\.hg/ - [F,L]

  # 保持现有文件/目录的直接访问
  RewriteCond %{REQUEST_FILENAME} -f [OR]
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteRule ^ - [L]

  # 所有请求路由到 index.php
  RewriteRule ^ index.php [L]
</IfModule>

# 额外的安全头
<IfModule mod_headers.c>
  Header set X-Content-Type-Options "nosniff"
  Header set X-XSS-Protection "1; mode=block"
  Header set X-Frame-Options "SAMEORIGIN"
</IfModule>

# 禁止目录列表
Options -Indexes

# 保护敏感文件
<FilesMatch "^\.">
  Order allow,deny
  Deny from all
</FilesMatch>

<FilesMatch "\.(db|sqlite|sqlite3|env|log|ini|sh|bash)$">
  Order allow,deny
  Deny from all
</FilesMatch>
```

如果站点部署在二级目录，例如 `/b2bwebsite/`，请把 `RewriteBase` 改成对应子路径：

```apache
RewriteBase /b2bwebsite/
```

#### Nginx

**根目录部署配置：**

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/html;
    index index.php;

    # 禁止访问敏感目录
    location ^~ /#data/ {
        deny all;
        return 403;
    }

    # 禁止访问版本控制目录
    location ~ /\. {
        deny all;
        return 403;
    }

    # 禁止访问敏感文件
    location ~* \.(db|sqlite|sqlite3|env|log|ini|sh|bash)$ {
        deny all;
        return 403;
    }

    # 静态资源缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # PHP 处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;  # 根据实际 PHP 版本调整
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # 安全优化
        fastcgi_hide_header X-Powered-By;
    }

    # 伪静态规则 - 所有请求转发到 index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # 安全响应头
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
}
```

**二级目录部署配置（如 `/b2b/`）：**

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/html;
    index index.php;

    location ^~ /b2b/#data/ {
        deny all;
        return 403;
    }

    location ~ /b2b/\. {
        deny all;
        return 403;
    }

    location ~* /b2b/.*\.(db|sqlite|sqlite3|env|log|ini|sh|bash)$ {
        deny all;
        return 403;
    }

    location ~* /b2b/.*\.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    location ~ /b2b/\.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location /b2b/ {
        try_files $uri $uri/ /b2b/index.php?$query_string;
    }
}
```

### 5. 首次访问初始化

首次访问首页或后台时，程序会自动：

- 创建 `#data/site.db`
- 初始化 `users`、`settings`、`products`、`posts`、`inquiries`、`messages` 等表
- 写入默认管理员账号
- 自动补齐缺失字段并执行兼容迁移

### 6. 登录后台

- 后台地址：`/admin/login`
- 默认账号：`admin`
- 默认密码：`admin123`

首次登录后应立即修改密码。

## 部署注意事项

- 实际数据库目录是 `#data`，不是 `data`
- 生产环境必须禁止 Web 直接访问 `#data`
- `uploads` 目录用于产品图、站点图片和媒体库文件
- 应用已支持二级目录部署，`APP_BASE_PATH` 会在入口文件中自动计算
- 如果宿主环境不支持 URL 重写，仍可使用 `index.php?r=/products` 这类兼容访问方式

## 目录结构

```text
.
├── index.php                 # 应用入口
├── app/
│   ├── Controllers/          # 前后台控制器
│   ├── Core/                 # Router / Database / Auth / Media 等核心类
│   ├── Models/               # 数据模型
│   ├── views/admin/          # 后台视图与静态资源
│   ├── Helpers/              # 辅助函数目录
│   │   ├── Helpers.php       # 全局辅助函数
│   │   └── SecurityHelper.php # 安全辅助函数
│   └── routes.php            # 路由注册
├── themes/
│   └── default/              # 默认前台主题
├── #data/                    # SQLite 数据目录（禁止 Web 访问）
├── uploads/                  # 上传文件目录
└── Documents/                # 项目说明与历史报告
```

## 核心数据模型

### 表结构摘要

| 表名 | 用途 |
|------|------|
| users | 后台用户、角色和权限 |
| settings | 网站配置项键值存储 |
| products | 产品主表 |
| product_prices | 产品阶梯价格 |
| product_categories | 分类表，用 type 区分 product 和 post |
| posts | 内容主表，用 post_type 区分 post、case、page |
| inquiries | 产品询盘 |
| messages | 联系留言 |
| media_files | 媒体库索引 |
| cases | 旧版案例表，仅用于兼容迁移 |

### 内容模型说明

- 产品使用 `products` 表单独存储
- 博客、案例、自定义页面统一进入 `posts` 表
- `posts.post_type` 支持三种类型：`post`、`case`、`page`
- 分类统一使用 `product_categories` 表，通过 `type` 区分产品分类和文章分类

这种设计意味着新增内容类型时，优先考虑复用 `posts` 表而不是再增加独立表。

## 路由概览

### 前台路由

| 路径 | 说明 |
|------|------|
| `/` | 首页 |
| `/products` | 产品列表 |
| `/product/:slug` | 产品详情 |
| `/cases` | 案例列表 |
| `/case/:slug` | 案例详情 |
| `/blog` | 博客列表 |
| `/blog/:slug` | 博客详情 |
| `/page/:slug` | 自定义页面详情 |
| `/about` | 关于我们 |
| `/contact` | 联系我们，支持 GET/POST |
| `/inquiry` | 产品询盘提交 |
| `/robots.txt` | Robots 协议 |
| `/sitemap.xml` | 站点地图 |

### 后台路由

| 路径前缀 | 说明 |
|----------|------|
| `/admin/login` | 登录 |
| `/admin` | 仪表盘 |
| `/admin/products` | 产品管理 |
| `/admin/product-categories` | 产品分类 |
| `/admin/posts` | 博客管理 |
| `/admin/post-categories` | 文章分类 |
| `/admin/cases` | 案例管理 |
| `/admin/pages` | 自定义页面管理 |
| `/admin/messages` | 留言管理 |
| `/admin/inquiries` | 询盘管理 |
| `/admin/media` | 媒体库 |
| `/admin/staff` | 员工管理 |
| `/admin/profile` | 个人资料 |
| `/admin/settings-*` | 系统设置各分组页面 |

## 权限与安全

### 认证方式

- 后台登录成功后，用户信息写入 session
- 密码使用 `password_hash` 和 `password_verify` 处理
- 管理员拥有全部权限
- staff 用户根据 `permissions` 字段决定可访问模块

### 已实现的安全措施

- POST 操作统一使用 CSRF token 校验
- 后台路由访问前执行认证与权限检查
- 媒体目录路径会做规范化与非法字符校验
- 上传文件按 MIME 类型和大小限制进行校验

### 当前权限分组

- `products`：产品和产品分类
- `cases`：案例
- `blog`：博客、页面、文章分类
- `inbox`：留言和询盘
- `settings`：系统设置
- `staff`：员工管理

## 主题与模板

前台主题位于 `themes` 目录下。当前默认主题为 `themes/default`，运行时会自动加载：

- `header.php`
- `footer.php`
- 对应页面模板，例如 `home.php`、`product_detail.php`、`post_list.php`
- `functions.php` 里的主题辅助函数

后台主题不走前台主题系统，模板固定放在 `app/views/admin`。

切换主题时，只需：

1. 在 `themes` 下新建主题目录
2. 提供至少 `header.php`、`footer.php` 和需要覆盖的页面模板
3. 在后台系统设置中修改 `theme`

## 开发说明

### 新增前台页面

1. 在 `app/Controllers/SiteController.php` 中添加方法
2. 在 `app/routes.php` 中注册路由
3. 在当前主题目录创建对应模板

### 新增后台功能

1. 在 `app/Controllers/AdminController.php` 中添加方法
2. 在 `app/routes.php` 中注册后台路由
3. 在 `app/views/admin` 中新增视图文件
4. 如有数据变更，在对应 Model 中封装数据库操作

### 修改数据库结构

统一在 `app/Core/Database.php` 中维护：

- `initSchema()` 负责首次建表
- `ensureColumns()` 负责增量补列和兼容迁移

### 关键辅助函数

全局通用函数位于 `app/Helpers/Helpers.php`，主要包括：

- `url()`、`base_url()`、`asset_url()`
- `csrf_token()`、`csrf_check()`
- `slugify()`
- `format_date()`
- `get_head_code()`、`get_footer_code()`
- `get_google_translate_widget()`

## 媒体与文件存储

- 普通上传目录为 `uploads/YYYYMM`
- 媒体库支持多级目录
- 媒体元数据会同步写入 `media_files` 表
- 图片上传接口用于富文本编辑器与后台资源选择
- 媒体库支持目录浏览、搜索、按类型筛选、批量删除

## 维护建议

### 生产环境建议

1. 立即修改默认管理员密码
2. 限制 `#data` 目录的 Web 访问（通过 Nginx/Apache 配置）
3. 定期备份 `#data/site.db` 和 `uploads`
4. 启用 HTTPS
5. 给 staff 账号分配最小必要权限

### 升级和兼容

- 当前项目已包含自动补列逻辑，适合小版本平滑升级
- 如果历史数据中仍存在 `cases` 表，程序会在启动时尝试迁移到 `posts`
- 若要新增设置项，可直接写入 `settings` 表，无需变更表结构

## License

MIT License.
