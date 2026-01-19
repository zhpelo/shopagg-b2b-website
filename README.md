# B2B 企业官网（PHP 8.4 + SQLite）

单文件 `index.php` 实现的可运营 B2B 官网，前台与后台统一使用 Bulma CSS，支持产品、成功案例、博客、询单与联系表单，内置 SEO（sitemap/robots）与伪静态路由，支持多主题切换。

## 环境要求
- PHP 8.4
- SQLite3 扩展
- Apache 或 Nginx（已包含 Apache `.htaccess` 重写）

## 启动方式
1. 把项目放到 Web 根目录（如 Apache `DocumentRoot`）。
2. 确保 `data/` 可写（首次运行自动创建数据库）。
3. 访问首页 `/`。

> 首次访问会自动初始化数据库与默认主题（`themes/default`）。

## 默认管理员账号
- 用户名：`admin`
- 密码：`admin123`
- 后台入口：`/admin`

## 前台功能
- 产品展示：`/products`、`/product/{slug}`
- 成功案例：`/cases`、`/case/{slug}`
- 博客文章：`/blog`、`/blog/{slug}`
- 联系表单：`/contact`
- 询单提交：产品详情页内

## 后台功能
- 产品、案例、博客的增删改查
- 询单与联系表单信息查看
- 站点信息设置与主题切换

## 伪静态路由
Apache 已提供 `.htaccess`，会将请求重写到 `index.php`。  
如未配置重写，可使用 `index.php?r=/path` 访问。

## 主题系统
默认主题位于 `themes/default`，可新建主题目录并在后台设置中切换：
1. 创建新目录：`themes/your-theme`
2. 复制默认模板文件（`header.php`、`footer.php`、`home.php`、`list.php`、`detail.php`、`contact.php`、`thanks.php`、`404.php`）
3. 后台 `Settings` 中将 `Theme` 设置为 `your-theme`

## SEO
- `robots.txt`：`/robots.txt`
- `sitemap.xml`：`/sitemap.xml`

## 目录结构
```
/index.php
/data/site.db
/themes/default/*.php
/.htaccess
```

