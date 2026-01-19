# B2B 企业官网 (MVC 架构版)

基于 PHP 8.4 + SQLite3 构建的现代 B2B 企业官网系统。采用标准 MVC 设计模式重新架构，代码清晰、易于扩展，专为海外运营设计。

## 核心特性
- **标准 MVC 架构**：逻辑 (Controller)、数据 (Model)、视图 (View) 彻底分离。
- **UI 框架**：前后台均采用 **Bulma CSS**，现代、简洁、响应式。
- **多语言支持**：支持中英文切换，后台可设置默认语言。
- **B2B 增强**：
    - **阶梯价格**：支持产品多级起订量与对应单价设置。
    - **WhatsApp 集成**：产品详情页一键联系。
    - **多图管理**：产品支持 1-6 张图片上传与轮播展示（含灯箱效果）。
- **富文本编辑**：集成 **Quill Editor**，支持后台图片上传至本地服务器。
- **SEO 优化**：内置伪静态路由、自动生成 `sitemap.xml` 和 `robots.txt`。

## 环境要求
- PHP 8.4+
- SQLite3 扩展
- Apache 或 Nginx (已内置 Apache `.htaccess`)

## 目录结构
```text
/
├── app/                  # 核心逻辑
│   ├── Core/             # 框架核心 (路由、数据库基类)
│   ├── Models/           # 数据模型 (产品、设置、案例等)
│   ├── Controllers/      # 控制器 (前台、后台管理)
│   ├── views/            # 后台管理视图
│   └── Helpers.php       # 全局辅助函数
├── themes/               # 前台主题 (支持多主题切换)
├── data/                 # 数据库存储
├── uploads/              # 本地上传附件
├── index.php             # 统一入口
└── .htaccess             # 伪静态规则
```

## 快速开始
1. 将项目上传至 Web 服务器。
2. 确保 `data/` 和 `uploads/` 目录具备读写权限。
3. 访问首页即可自动初始化数据库。

### 默认管理员信息
- **后台入口**：`/admin`
- **用户名**：`admin`
- **密码**：`admin123`

## 开发者指南
### 新增页面
1. 在 `app/Controllers/SiteController.php` 中添加方法。
2. 在 `index.php` 中注册对应路由。
3. 在 `themes/default/` 中创建相应的模板文件。

### 新增模型
1. 在 `app/Models/` 下继承 `BaseModel` 创建新类。
2. 使用 `Database::getInstance()` 进行数据库交互。

## 伪静态配置
系统默认使用伪静态。如环境不支持，可通过 `index.php?r=/your-path` 形式访问。

## 授权
本项目可用于商业运营，建议在正式上线前修改管理员默认密码及站点设置。
