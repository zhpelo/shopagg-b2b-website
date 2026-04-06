# SHOPAGG B2B Website

一款专为外贸企业打造的现代化 B2B 官网系统。基于 PHP 8.4 + SQLite3 构建，采用 MVC 架构，零依赖、开箱即用。

**开发者**: [SHOPAGG](https://www.shopagg.com)

---

## ✨ 功能特性

### 🏠 前台展示
- **产品展示** - 分类管理、多图轮播、阶梯定价、规格参数
- **案例展示** - 成功案例图文展示
- **新闻博客** - 文章分类、内容管理
- **公司介绍** - 企业简介、资质证书、公司展示
- **联系我们** - 在线留言、询单表单
- **多语言** - 中英文切换，可扩展更多语言

### 🔧 后台管理
- **仪表盘** - 数据概览、快捷操作
- **产品管理** - 产品 cases、分类管理、多图上传、阶梯价格
- **内容管理** - 文章管理、案例管理、分类管理
- **媒体库** - 图片上传、预览、删除、存储统计
- **收件箱** - 联系留言、产品询单、状态跟踪
- **员工管理** - 多账号、角色权限控制
- **系统设置** - 网站信息、Logo/Favicon、SEO、公司资料

### 🚀 技术亮点
- **MVC 架构** - Controller / Model / View 分离，代码清晰
- **零依赖** - 纯 PHP 实现，无需 Composer
- **SQLite3** - 轻量数据库，无需安装 MySQL
- **响应式 UI** - Bulma CSS 框架，适配移动端
- **富文本编辑** - Jodit Editor 集成，自定义媒体库支持
- **SEO 友好** - 伪静态路由、Sitemap、Robots.txt
- **安全性** - CSRF 防护、密码加密、权限控制

---

## 📋 环境要求

| 要求 | 版本 |
|------|------|
| PHP | 8.0+ (推荐 8.4) |
| SQLite3 | 扩展已启用 |
| Web 服务器 | Apache / Nginx |

---

## 🛠️ 快速安装

### 1. 下载部署
```bash
# 克隆项目
git clone https://github.com/zhpelo/shopagg-b2b-website.git

# 或直接上传至服务器
```

### 2. 设置权限
```bash
chmod -R 755 #data/
chmod -R 755 uploads/
```

### 3. 配置 Web 服务器

**Apache** (已内置 `.htaccess`):
```apache
# 确保启用 mod_rewrite
```

**Nginx**:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 4. 访问网站
- 前台首页: `http://your-domain.com/`
- 后台管理: `http://your-domain.com/admin`

---

## 🔑 默认账号

| 角色 | 用户名 | 密码 |
|------|--------|------|
| 管理员 | admin | admin123 |

> ⚠️ **重要**: 首次登录后请立即修改默认密码！

---

## 📁 目录结构

```
├── app/                    # 应用核心
│   ├── Controllers/        # 控制器
│   │   ├── AdminController.php   # 后台控制器
│   │   ├── SiteController.php    # 前台控制器
│   │   └── BaseController.php    # 基础控制器
│   ├── Models/             # 数据模型
│   │   ├── Product.php     # 产品模型
│   │   ├── Category.php    # 分类模型
│   │   ├── PostModel.php   # 文章模型
│   │   ├── CaseModel.php   # 案例模型
│   │   ├── Inquiry.php     # 询单模型
│   │   ├── Message.php     # 留言模型
│   │   ├── User.php        # 用户模型
│   │   └── Setting.php     # 设置模型
│   ├── Core/               # 框架核心
│   │   ├── Router.php      # 路由器
│   │   ├── Database.php    # 数据库 (含表结构)
│   │   └── Controller.php  # 控制器基类
│   ├── views/admin/        # 后台视图
│   └── Helpers.php         # 辅助函数
├── themes/                 # 前台主题
│   └── default/            # 默认主题
│       ├── header.php      # 头部
│       ├── footer.php      # 底部
│       ├── home.php        # 首页
│       ├── product_*.php   # 产品页面
│       ├── post_*.php      # 文章页面
│       ├── case_*.php      # 案例页面
│       ├── about.php       # 关于我们
│       ├── contact.php     # 联系我们
│       └── lang/           # 语言包
├── data/                   # 数据库文件
├── uploads/                # 上传文件
├── index.php               # 统一入口
└── .htaccess               # Apache 伪静态
```

---

## 🔌 路由列表

### 前台路由
| 路由 | 说明 |
|------|------|
| `/` | 首页 |
| `/products` | 产品列表 |
| `/product/:slug` | 产品详情 |
| `/cases` | 案例列表 |
| `/case/:slug` | 案例详情 |
| `/blog` | 博客列表 |
| `/blog/:slug` | 文章详情 |
| `/about` | 关于我们 |
| `/contact` | 联系我们 |
| `/sitemap.xml` | 站点地图 |
| `/robots.txt` | 爬虫协议 |

### 后台路由
| 路由 | 说明 |
|------|------|
| `/admin` | 仪表盘 |
| `/admin/products` | 产品管理 |
| `/admin/posts` | 文章管理 |
| `/admin/cases` | 案例管理 |
| `/admin/media` | 媒体库 |
| `/admin/messages` | 联系留言 |
| `/admin/inquiries` | 询单管理 |
| `/admin/staff` | 员工管理 |
| `/admin/settings` | 系统设置 |

---

## 🎨 主题开发

### 创建新主题
1. 复制 `themes/default/` 为 `themes/your-theme/`
2. 修改模板文件
3. 后台「系统设置」切换主题

### 模板变量
```php
$site       // 网站设置
$seo        // SEO 数据
$lang       // 当前语言
$languages  // 可用语言
```

### 辅助函数
```php
h($str)           // HTML 转义
t($key)           // 翻译文本
format_date($dt)  // 格式化日期
base_url()        // 网站根 URL
```

---

## 📝 开发指南

### 新增页面
1. `SiteController.php` 添加方法
2. `index.php` 注册路由
3. `themes/default/` 创建模板

### 新增模型
```php
<?php
namespace App\Models;

class YourModel extends BaseModel {
    public function getAll(): array {
        return $this->fetchAll("SELECT * FROM your_table");
    }
}
```

### 新增数据表
编辑 `app/Core/Database.php` 的 `initSchema()` 方法。

---

## 🔒 安全建议

1. **修改默认密码** - 首次部署后立即修改
2. **保护数据目录** - 禁止 Web 访问 `#data/` 目录
3. **HTTPS** - 生产环境启用 SSL 证书
4. **定期备份** - 备份 `#data/site.db` 和 `uploads/`

---

## 📄 开源协议

MIT License - 可自由用于个人和商业项目。

---

## 🤝 技术支持

- 官网: [https://www.shopagg.com](https://www.shopagg.com)
- 问题反馈: 提交 GitHub Issue

---

**Made with ❤️ by SHOPAGG**
