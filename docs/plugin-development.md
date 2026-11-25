# SHOPAGG CMS 插件开发指南

> **归属仓库**：`shopagg-b2b-website`
> **适用范围**：插件开发者与 AI 助手；硬性约束速查见[插件规范](plugin-specification.md)，Manifest 校验源码见 `app/Plugins/PluginManifest.php`。
> **相关文档**：[系统架构](architecture.md) · [插件规范](plugin-specification.md)

---

## 1. 运行模型与脚手架

SHOPAGG CMS Plugin API 1.0 是进程内 PHP 插件系统，最低运行环境为 **PHP 8.1+ / SQLite3**，不依赖站点运行 Composer（第三方依赖需随 ZIP 打包进插件 `vendor/`）。

常用 CLI 命令（在仓库根目录执行）：

```bash
php shopagg plugin:make my-plugin
php shopagg plugin:validate plugin-dev/my-plugin
php shopagg plugin:test plugin-dev/my-plugin
php shopagg plugin:pack plugin-dev/my-plugin
```

- 参考示例插件：`examples/plugins/hello-shopagg`。
- 正常 HTTP 请求仅读取编译缓存 `storage/cache/plugins.php`，不会扫描插件目录。
- 安装后的 PHP 源码保存在受保护的 `storage/plugins/`，允许公开的静态资源发布至独立版本化公开目录（不在 `uploads/` 下）。

---

## 2. Manifest（`plugin.json`）

```json
{
  "schema_version": 1,
  "id": "member-center",
  "name": "Member Center",
  "vendor": "Example",
  "version": "1.0.0",
  "description": "Front-end member identity provider",
  "requires": { "shopagg": ">=1.3.0", "php": ">=8.1.0" },
  "autoload": { "psr4": { "Example\\Member\\": "src/" } },
  "lifecycle": "Example\\Member\\Plugin",
  "routes": [],
  "events": {},
  "filters": {},
  "slots": {},
  "provides_services": {},
  "requires_services": {},
  "jobs": {},
  "settings": {},
  "permissions": [],
  "admin_menu": [],
  "dependencies": [],
  "conflicts": []
}
```

- 插件 ID 必须匹配 `^[a-z0-9]+(?:-[a-z0-9]+)*$`；版本使用完整 SemVer。
- 所有处理器统一写作 `完整类名@方法名`；依赖约束支持 `=`、`>`、`>=`、`<`、`<=`、`^`。

---

## 3. 路由、中间件与布局

```json
{
  "method": "POST",
  "path": "/payment/webhook/:gateway",
  "name": "payment.webhook",
  "handler": "Example\\Payment\\Webhook@handle",
  "middleware": ["webhook"],
  "layout": "standalone"
}
```

处理器统一签名：

```php
public function handle(Request $request, PluginContext $plugin, string $gateway): Response
{
    return Response::json(['ok' => true]);
}
```

- **内置中间件**：`admin`、`permission:plugin.{id}.{perm}`、`csrf`、`member`、`webhook`，也支持自定义 `Class@method`。
- **布局模式**：
  - `layout: "theme"`：将字符串响应包裹在当前前台主题中渲染。
  - `layout: "admin"`（配合静态 `title`）：后台 GET 页面只返回内容片段，由核心统一渲染侧栏、顶部栏与插件资源；`Response`、JSON、重定向与下载响应不被后台布局包裹。
  - `layout: "standalone"`：独立输出（适合 Webhook、API 或自定义页）。
- **静态资源按需加载**：在 `assets` 中通过 `location`（`head`/`footer`）和 `routes` 通配符声明按路由加载。
- 插件不得覆盖核心保留路由或与其他已启用插件冲突。

---

## 4. 数据迁移与表隔离

迁移文件放在插件 `migrations/` 目录，按文件名顺序执行：

```php
return new class {
    public function up(PluginSchema $schema, PluginContext $plugin): void
    {
        $schema->create('orders', [
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'number' => 'TEXT NOT NULL UNIQUE',
            'status' => "TEXT NOT NULL DEFAULT 'pending'",
        ], ['status' => ['status']]);
    }
};
```

- 逻辑表名自动映射为 `p_{plugin_id}_{table}`，代码一律通过 `$plugin->database()->table('orders')` 获取真实表名。
- 严禁修改核心表或直接读写其他插件表；跨插件数据交互必须通过服务契约完成。

---

## 5. Event、Filter、Slot 与表单拦截

```php
$plugin->events()->dispatch('order.paid', $order);
$price = $plugin->filters()->apply('product.price', $price, ['product' => $product]);
$html = $plugin->slots()->render('site.content.after', ['page' => 'product']);
```

- 同步监听器应快速返回；外部 HTTP 请求、批量导入、通知发送等耗时逻辑必须投递后台任务。
- **核心表单拦截（`form.before_validate`）**：如需阻止核心询盘/联系表单写入，抛出 `App\Plugins\Exceptions\FormValidationException`，核心将中止写入并 `303` 返回来源页，插件可通过 `$plugin->session()` 保存一次性提示并通过 Slot 展示。禁止在插件中调用 `exit`。

---

## 6. 服务组合（Contracts）

通过 `provides_services` / `requires_services` 按接口版本契约解耦插件协作（契约定义见 `App\Plugins\Contracts` 与 `App\Plugins\DTO`）：

| 类型 | 标准服务契约 |
| --- | --- |
| 单提供者 | `member.identity`、`order.manager`、`points.manager`、`cart.manager` |
| 多提供者 | `payment.gateway`、`notification.channel`、`form.type`、`file.converter`、`data.importer`、`data.exporter`、`data.collector` |

启用插件前核心自动校验服务依赖与版本约束；运行时通过 `$plugin->services()->get(...)` 或 `all(...)` 调用。

---

## 7. 分片任务与周期任务

耗时或大批量操作必须实现 `ChunkedJobInterface` 并使用游标保存进度：

```php
final class ImportJob implements ChunkedJobInterface
{
    public function handle(JobContext $job, mixed $cursor): JobResult
    {
        return $hasMore ? JobResult::continue($nextCursor, $progress) : JobResult::complete();
    }
}
```

- 投递：`$plugin->jobs()->dispatch('catalog.import', ImportJob::class . '@handle', $payload, $idempotencyKey)`。
- 支持机会式调度、后台手动触发、Cron URL 与 `php shopagg plugin:jobs`，并支持周期任务配置、失败重试、任务取消与超时锁自动恢复。停用插件后待执行任务自动暂停调度。

---

## 8. 后台菜单融合规范

后台采用业务分组融合模式，通过 `admin_menu` 注册入口（严禁再使用已弃用的 `admin.navigation` Slot）：

```json
"admin_menu": [{
  "id": "orders",
  "section": "commerce",
  "order": 20,
  "label": "订单",
  "url": "/my-plugin/manage",
  "icon": "cart-shopping",
  "permission": "plugin.my-plugin.manage",
  "active_patterns": ["/my-plugin/manage*"]
}]
```

- 允许的 `section`：`inbox`、`catalog`、`commerce`、`content`、`appearance`、`extensions`、`system`（未声明时默认归入 `extensions`）。
- 权限过滤在分组渲染前执行，无可见子项的分组自动隐藏。

---

## 9. 升级、回滚与兼容性

- Plugin API 遵循语义化版本，同主版本保持向后兼容。
- 升级时若新增依赖、路由、权限或迁移失败，核心不会切换激活版本，原运行版本不受影响。
- 可通过后台或 `php shopagg plugin:rollback <id> [version]` 原子回滚代码版本；由于数据库迁移不自动回滚，所有插件迁移必须保持**向前兼容**。
- 卸载插件默认保留数据，仅在后台显式执行“永久清除”时删除插件表与配置。
