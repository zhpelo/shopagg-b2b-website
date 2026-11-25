# SHOPAGG CMS 模板开发指南

> **归属仓库**：`shopagg-b2b-website`
> **适用范围**：主题开发者、设计师与 AI 助手；新建或修改 `themes/` 目录下的主题前阅读，参考实现见 `themes/default/`。
> **相关文档**：[系统架构](architecture.md) · [后台使用指南](admin-guide.md)

---

## 1. 六项核心原则

1. **内容与视图解耦**：所有客户可改动的文案、媒体、颜色、开关与选品配置写入 `blocks.php`，模板文件仅负责读取与渲染。
2. **商品必用选择器**：任何商品展示区块必须使用 `product_picker` 弹窗，严禁用普通文本字段手填产品 ID 或手写静态商品卡片。
3. **媒体必用选择器**：图片、背景图、Logo、视频等媒体必须使用 `image` 或 `media` 字段，严禁让用户手填路径。
4. **轮播必用下拉关联**：轮播区块必须从 `/admin/appearance/sliders` 读取活跃轮播图列表并以 `select` 下拉选择，默认选中第一个可用轮播图。
5. **Tailwind 优先**：页面视觉与响应式布局优先使用 Tailwind CSS 类名，`style.css` 仅保留主题元数据与少量必需补充样式。
6. **默认安全输出**：动态文本一律使用 `h()`，多行文本使用 `nl2br(h(...))`，站内链接用 `url()`，媒体路径用 `asset_url()`。

---

## 2. 标准主题目录结构

每个主题独立存放在 `themes/{theme_name}/`：

```text
themes/{theme_name}/
├── style.css            # 主题元数据头（必须含 Theme Name）与少量补充样式
├── screenshot.jpg       # 后台主题预览封面图
├── blocks.php           # 后台模板区块与默认值定义（返回数组）
├── functions.php        # 主题级辅助函数（产品卡片、媒体渲染、轮播读取等）
├── header.php           # HTML 头部、Tailwind 品牌色注入、顶部导航
├── footer.php           # 页脚、浮动联系组件、全局脚本
├── home.php             # 首页区块编排
├── product_list.php     # 产品列表页
├── product_detail.php   # 产品详情页
├── post_list.php / post_detail.php
├── case_list.php / case_detail.php
├── about.php / contact.php / page_detail.php
├── list.php / thanks.php / 404.php
```

`style.css` 顶部元数据模板：

```css
/*
Theme Name: Industrial Pro
Theme URI: https://shopagg.org
Author: SHOPAGG
Description: B2B industrial equipment theme for SHOPAGG CMS.
Version: 1.0.0
License: MIT
*/
```

---

## 3. 模板区块与字段体系

### 3.1 默认值与用户覆盖

系统自动合并 `themes/{theme}/blocks.php`（默认值）与 `storage/blocks/{theme}.php`（用户保存值）。**默认值必须开箱即可完整渲染**，不得依赖用户先去后台配置。

模板读取方式：

```php
<h2><?= h(block('home_about', 'heading')) ?></h2>
<p><?= nl2br(h(block('home_about', 'text'))) ?></p>
<?php $about = block_all('home_about'); ?>
```

### 3.2 区块命名约定（`snake_case`）

- 全局：`brand_colors`、`header`、`footer`、`float_contact`
- 首页：`home_hero`、`home_featured`、`home_about`、`home_why`、`home_applications`、`home_process`、`home_faq`、`home_cta`
- 内页：`page_about_*`、`page_contact_*`、`product_*`、`post_*`、`case_*`

### 3.3 支持的字段类型

| 字段类型 | 后台控件 | 适用场景 |
| --- | --- | --- |
| `text` / `textarea` | 单行 / 多行文本 | 标题、标签、按钮文字、段落描述 |
| `url` | 链接输入 | 按钮与社媒链接（支持站内相对路径） |
| `number` / `range` | 数字 / 滑块 | 展示数量、统计数值、圆角与透明度（配合 `min`/`max`/`step`） |
| `toggle` | 布尔开关 | 区块显隐、功能开关 |
| `color` | 颜色选择器 | 全局品牌色与表面色 |
| `image` | 图片选择器弹窗 | 单纯图片、背景图、Logo |
| `media` | 文件选择器弹窗 | 图片或视频（必须配合 `media_type_key` 与 `allowed`: `image`/`video`/`all`） |
| `product_picker` | 商品选择器弹窗 | 热销产品、精选产品、相关产品（配合 `limit`、`status`） |
| `select` | 下拉框 | 轮播图选择（`slider_slug`）、布局模式 |
| `icon` | 图标类选择 | Font Awesome 图标类名 |
| `hidden` | 隐藏字段 | `media_type_key` 或旧主题兼容兜底字段 |

---

## 4. 三类关键区块标准写法

### 4.1 产品展示区块（强制 `product_picker`）

```php
// blocks.php
'product_ids' => [
    'type' => 'product_picker',
    'label' => '勾选商品列表',
    'default' => '',
    'limit' => 8,
    'status' => 'active',
],
```

模板读取时必须带最新上架商品兜底与空状态文案：

```php
<?php
$ids = trim(block('home_featured', 'product_ids'));
$products = $ids !== '' ? get_products_by_ids($ids, true) : [];
if (empty($products)) {
    $products = get_products(['limit' => 8, 'status' => 'active']);
}
?>
```

### 4.2 图片与视频混合媒体（`media`）

```php
// blocks.php
'main_media_type' => ['type' => 'hidden', 'label' => '主媒体类型', 'default' => 'image'],
'main_media' => [
    'type' => 'media',
    'label' => '主媒体',
    'default' => '/uploads/default-factory.jpg',
    'media_type_key' => 'main_media_type',
    'allowed' => 'all',
],
```

模板根据 `block('home_about', 'main_media_type', 'image')` 分支渲染 `<video>` 或 `<img>`，路径统一经 `h(asset_url(...))` 输出。

### 4.3 轮播图区块（关联 `App\Models\Slider`）

在 `blocks.php` 顶部读取当前活跃轮播图生成 `$sliderOptions`，在 `home_hero` 中以 `select` 字段暴露 `slider_slug`（默认取第一个可用轮播图），并在 `functions.php` 中封装 `get_slider_items(string $slug): array` 供模板调用。严禁暴露 `slide1_image`、`slide2_image` 等硬编码字段。

---

## 5. Header / Footer 与页面设计要点

- **站点全局设置优先**：`header.php` 与 `footer.php` 优先读取 `$site['logo']`、`$site['company_phone']`、`$site['company_email']`，为空时再回退区块默认值。
- **产品页真实数据**：`product_list.php` 与 `product_detail.php` 必须使用真实商品库数据（含真实 slug `url('/product/' . $product['slug'])`），区块只控制 Banner、按钮与辅助文案。
- **品牌色注入**：通过 `brand_colors` 区块将主色、深色、强调色注入 `tailwind.config` 或 CSS 变量，保证全站视觉统一。
- **复用函数**：产品卡片、媒体渲染、价格格式化与轮播读取统一抽离到 `functions.php`。

---

## 6. 开发与 AI 验收清单

开发或使用 AI 生成主题后，必须逐项核验：

- [ ] 主题目录文件完整，`style.css` 含元数据，`screenshot.jpg` 存在，`blocks.php` 可正常返回数组
- [ ] 所有 PHP 模板通过 `php -l` 语法检查
- [ ] 产品区块全部使用 `product_picker` 且带最新产品兜底，无手填 ID 或伪造商品卡片
- [ ] 图片/视频全部使用 `image` / `media` 选择器，`media` 已配 `media_type_key`
- [ ] 轮播区块使用 `select` 关联后台轮播图数据，默认选中第一项
- [ ] 全部动态输出经过 `h()` / `url()` / `asset_url()` 转义，空数据状态不报错
- [ ] 移动端、平板与桌面端无横向溢出或遮挡，视觉样式以 Tailwind 类名为主
