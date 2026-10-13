<div align="center">

<a href="https://shopagg.org/">
  <img src="https://shopagg.org/assets/images/lighthouse-logo.png" width="96" height="96" alt="灯塔 CMS Logo">
</a>

# 灯塔 CMS

**Lighthouse CMS · 面向中国工厂与外贸公司的开源 B2B 官网系统**

纯 PHP + SQLite · 自托管 · 可扩展 · MIT 开源

[官网](https://shopagg.org/) · [在线演示](https://demo.shopagg.org/) · [使用文档](https://shopagg.org/usage) · [开发文档](https://shopagg.org/developer) · [版本发布](https://shopagg.org/releases)

[简体中文](README.md) · [English](README.en.md)

[![Website](https://img.shields.io/badge/Website-shopagg.org-0FA5FC?logo=googlechrome&logoColor=white)](https://shopagg.org/)
[![Latest release](https://img.shields.io/github/v/release/zhpelo/shopagg-b2b-website?display_name=tag&sort=semver)](https://shopagg.org/releases)
[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)](https://shopagg.org/api/compatibility.json)
[![SQLite](https://img.shields.io/badge/SQLite3-003B57?logo=sqlite&logoColor=white)](https://shopagg.org/developer/data-model)
[![License: MIT](https://img.shields.io/badge/License-MIT-22C55E.svg)](LICENSE)

</div>

---

## 项目简介

灯塔 CMS 是一套面向 B2B 外贸业务的开源官网系统，将产品展示、内容运营、客户询盘、主题外观和插件扩展整合在一个轻量应用中。核心程序使用 PHP 8.1+ 与 SQLite3，不依赖 Composer 包，适合部署到普通虚拟主机、VPS 或企业自有服务器。

项目原名 **SHOPAGG B2B Website**。品牌现已更名为 **灯塔 CMS（Lighthouse CMS）**，GitHub 仓库地址和 `shopagg` 插件 CLI 名称继续沿用。

想先了解产品定位和完整能力，请访问：

- [灯塔 CMS 官网](https://shopagg.org/)
- [32 项产品能力清单](https://shopagg.org/capabilities)
- [在线演示站](https://demo.shopagg.org/)
- [当前稳定版与下载信息](https://shopagg.org/releases)

## 核心能力

| 领域 | 主要能力 | 进一步了解 |
|---|---|---|
| 产品与商品 | 分类、图集、四种价格模式、多规格 SKU、软删除 | [产品管理文档](https://shopagg.org/usage/product-management) |
| 内容与媒体 | 文章、案例、独立页面、富文本、媒体库 | [内容与外观文档](https://shopagg.org/usage/content-and-appearance) |
| 询盘与线索 | 联系留言、产品询盘、处理状态、CSV 导出 | [询盘与日常运营](https://shopagg.org/usage/inquiries) |
| 主题与外观 | 主题切换、可配置区块、菜单、轮播图 | [主题开发文档](https://shopagg.org/developer/themes) |
| 插件与扩展 | 路由、事件、过滤器、服务、任务、后台页面 | [插件开发文档](https://shopagg.org/developer/plugins) |
| 应用商店 | 浏览、安装和升级插件及 B2B 网站主题 | [设置与扩展](https://shopagg.org/usage/settings-and-extensions) |
| 运维与更新 | GitHub Release 更新、更新前备份、迁移、日志 | [运维文档](https://shopagg.org/developer/operations) |
| 权限与安全 | Staff 权限、CSRF、上传校验、安全响应头 | [安全边界](https://shopagg.org/developer/security) |
| SEO 与国际化 | SEO 字段、Sitemap、Open Graph、249 种在线翻译语言 | [完整能力清单](https://shopagg.org/capabilities) |

完整且可检索的功能状态以官网的[产品能力清单](https://shopagg.org/capabilities)为准，也可以直接读取[产品能力 JSON](https://shopagg.org/api/capabilities.json)。

## 界面预览

访问 [demo.shopagg.org](https://demo.shopagg.org/) 体验前台页面；后台功能和操作流程可从[使用文档](https://shopagg.org/usage)了解。

### 前台

<img alt="灯塔 CMS 前台页面" src="https://github.com/user-attachments/assets/2f9f289a-3ccf-48b5-a959-aaa3699cc9ce">

### 后台

| 仪表盘 | 产品管理 |
|:---:|:---:|
| <img alt="灯塔 CMS 仪表盘" src="https://github.com/user-attachments/assets/b6400b0b-f345-4321-9c90-5925c1e6b790"> | <img alt="灯塔 CMS 产品管理" src="https://github.com/user-attachments/assets/86918ca6-dd59-4fde-bacf-eef4dc668460"> |

| 媒体库 | 系统设置 |
|:---:|:---:|
| <img alt="灯塔 CMS 媒体库" src="https://github.com/user-attachments/assets/18a252ad-de48-4a3f-8fee5833cc6e"> | <img alt="灯塔 CMS 系统设置" src="https://github.com/user-attachments/assets/21f24c46-7716-47df-bdeb-0cf2228ba5fd"> |

## 快速开始

### 运行环境

| 项目 | 要求 |
|---|---|
| PHP | 8.1 或更高版本 |
| PHP 扩展 | `sqlite3`、`mbstring`、`fileinfo` |
| Web 服务器 | Apache（启用 `mod_rewrite`）或 Nginx |
| 可写目录 | `storage/`、`uploads/` |

最新兼容性信息可读取[兼容性 JSON](https://shopagg.org/api/compatibility.json)。完整的 Apache、Nginx 和生产环境配置见[安装与入门](https://shopagg.org/usage/getting-started)。

### 安装

```bash
git clone https://github.com/zhpelo/shopagg-b2b-website.git
cd shopagg-b2b-website
chmod -R 755 storage uploads
```

将站点根目录指向项目目录，然后访问网站。系统会自动创建 SQLite 数据库并执行待处理迁移。

后台默认入口为 `/admin/login`。初始账号通常为 `admin` / `admin123`，首次登录后请立即修改密码。

安装完成后建议继续阅读：

1. [首次登录与初始化](https://shopagg.org/usage/getting-started#first-login)
2. [产品管理与价格模式](https://shopagg.org/usage/product-management)
3. [内容、菜单、轮播与主题外观](https://shopagg.org/usage/content-and-appearance)
4. [插件、员工权限和程序更新](https://shopagg.org/usage/settings-and-extensions)
5. [上线检查与常见问题](https://shopagg.org/usage/launch-and-faq)

## 技术设计

- **纯 PHP MVC**：单入口、集中路由、控制器、模型和视图分层。
- **SQLite3**：站点数据默认保存在 `storage/site.db`，数据库结构通过迁移演进。
- **主题系统**：前台主题位于 `themes/{theme}`，内容区块可在后台维护。
- **Plugin API 1.0**：Manifest 驱动的插件运行时，支持缓存、隔离数据、任务和服务契约。
- **可迁移部署**：不绑定特定云平台，可部署到 Apache、Nginx 和常见 PHP 主机。

深入阅读：[系统架构](https://shopagg.org/developer/architecture) · [数据模型](https://shopagg.org/developer/data-model) · [应用开发](https://shopagg.org/developer/application-development)

### 目录结构

```text
shopagg-b2b-website/
├── index.php               # 应用入口
├── app/
│   ├── Controllers/        # 前后台控制器
│   ├── Core/               # 路由、数据库、认证、迁移等核心模块
│   ├── Models/             # SQLite 数据模型
│   ├── Plugins/            # Plugin API 1.0 运行时
│   ├── Migrations/         # 核心数据库迁移
│   ├── views/admin/        # 后台页面
│   └── routes.php          # 路由注册
├── themes/                 # 前台主题
├── plugin-dev/             # 本地插件开发目录
├── plugin-assets/          # 已发布的插件静态资源
├── storage/                # 数据库、缓存、备份和日志
├── uploads/                # 用户上传文件
└── shopagg                 # 插件开发 CLI
```

## 插件与主题开发

业务扩展优先使用 Plugin API，避免直接修改核心文件。插件 CLI 支持创建、校验、测试和打包：

```bash
php shopagg plugin:make my-plugin
php shopagg plugin:validate plugin-dev/my-plugin
php shopagg plugin:test plugin-dev/my-plugin
php shopagg plugin:pack plugin-dev/my-plugin
```

- [插件开发完整指南](https://shopagg.org/developer/plugins)
- [主题目录、区块字段和安全输出规范](https://shopagg.org/developer/themes)
- [控制器、模型、迁移和后台页面开发](https://shopagg.org/developer/application-development)
- [安全边界与发布前检查](https://shopagg.org/developer/security)

## 开发与验证

正式发行包已经包含后台所需的编译后 CSS。只有修改后台 Tailwind 类名或 `assets/admin/style.css` 时，才需要 Node.js：

```bash
npm ci
npm run build:admin-css
```

提交 PHP 改动前，至少运行相关语法检查；修改后台导航后可运行现有冒烟测试：

```bash
php tests/admin_navigation_smoke.php
```

更完整的开发约定与运维验收步骤见[开发文档](https://shopagg.org/developer)和[运维文档](https://shopagg.org/developer/operations)。

## 文档与机器可读资源

官网同时提供面向用户、开发者、搜索引擎和 AI 的资料：

| 资源 | 地址 |
|---|---|
| 官网首页 | [shopagg.org](https://shopagg.org/) |
| 使用文档 | [shopagg.org/usage](https://shopagg.org/usage) |
| 开发文档 | [shopagg.org/developer](https://shopagg.org/developer) |
| 产品能力清单 | [shopagg.org/capabilities](https://shopagg.org/capabilities) |
| 版本发布 | [shopagg.org/releases](https://shopagg.org/releases) |
| AI 项目摘要 | [llms.txt](https://shopagg.org/llms.txt) |
| AI 完整上下文 | [llms-full.txt](https://shopagg.org/llms-full.txt) |
| 项目信息 JSON | [api/project.json](https://shopagg.org/api/project.json) |
| 产品能力 JSON | [api/capabilities.json](https://shopagg.org/api/capabilities.json) |
| 文档索引 JSON | [api/docs/index.json](https://shopagg.org/api/docs/index.json) |
| 最新版本 JSON | [api/releases/latest.json](https://shopagg.org/api/releases/latest.json) |
| 静态 API OpenAPI | [api/openapi.json](https://shopagg.org/api/openapi.json) |

## 版本与更新

稳定版本通过 GitHub Release 发布，后台可以检查新版本、下载更新包，并在覆盖文件前创建备份。发布历史、下载地址和版本说明统一展示在[官网版本页](https://shopagg.org/releases)。

生产环境升级前请先阅读[程序更新、备份与故障恢复](https://shopagg.org/developer/operations)，并备份 `storage/site.db`、`uploads/` 和自定义主题。

## 参与贡献

欢迎提交 Issue 和 Pull Request：

1. Fork 本仓库并从最新代码创建分支。
2. 保持改动聚焦，并补充必要的验证。
3. 提交 Pull Request，说明问题、行为变化和验证方式。

准备贡献前建议阅读[开发文档](https://shopagg.org/developer)，了解核心架构、插件边界、主题规范和安全要求。

## License

[MIT License](LICENSE) — 可自由用于个人和商业项目，也可以修改与分发。

---

<div align="center">

了解产品、阅读文档或获取最新版本，请访问 **[灯塔 CMS 官网](https://shopagg.org/)**。

</div>
