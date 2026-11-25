# SHOPAGG CMS 插件规范

> **归属仓库**：`shopagg-b2b-website`
> **适用范围**：面向插件开发者与 AI 助手的硬性约束清单；详细教程见[插件开发指南](plugin-development.md)，Manifest 校验逻辑以 `app/Plugins/PluginManifest.php` 为准。

---

## 1. 硬性规则清单

### 环境与目录
1. 目标运行环境固定为 **PHP 8.1+ / SQLite3**，不得要求目标站点运行 Composer；第三方依赖随包打包至插件 `vendor/`。
2. 优先使用 `php shopagg plugin:make <id>` 或参照 `examples/plugins/hello-shopagg` 初始化目录。
3. 严禁修改 `app/`、`themes/`、`index.php` 或核心迁移文件；插件数据表只能通过 `PluginSchema` 创建。

### Manifest 与命名
4. `plugin.json` 必须通过 `php shopagg plugin:validate <dir>` 校验。
5. 插件 ID 严格匹配 `^[a-z0-9]+(?:-[a-z0-9]+)*$`，版本使用完整 SemVer。
6. 所有类归属插件独立 PSR-4 命名空间，处理器一律使用 `Class@method` 格式。

### 路由、权限与后台集成
7. 路由处理器签名为 `(Request $request, PluginContext $plugin, ...$params)`，返回 `Response|array|string`。
8. 后台写操作路由必须同时声明 `admin`、`permission:plugin.{id}.{permission}` 与 `csrf` 中间件。
9. 后台 GET 页面声明 `layout: "admin"` 与静态 `title`，仅返回内容片段；JSON、重定向与下载直接返回 `Response`。
10. 后台入口必须通过 `admin_menu` 声明 `id`、受控 `section`（`inbox` / `catalog` / `commerce` / `content` / `appearance` / `extensions` / `system`）、`order` 与 `active_patterns`；禁止使用已弃用的 `admin.navigation` Slot。

### 扩展点、任务与兼容
11. 页面修改使用 Filter / Slot，业务通知使用 Event，耗时与大批量操作使用 `ChunkedJobInterface`。
12. 会员、订单、支付、积分、通知等跨插件能力必须基于 `App\Plugins\Contracts`，通过 `provides_services` / `requires_services` 声明接口版本。
13. 仅允许在 `form.before_validate` 中抛出 `App\Plugins\Exceptions\FormValidationException` 中止核心表单写入；严禁在其他钩子中调用 `exit`。
14. 升级新增字段或表时一律新增迁移文件，严禁修改已发布迁移，且保持向前兼容以支持代码原子回滚。

---

## 2. `PluginContext` 常用 SDK 速查

| 分类 | 调用入口 |
| --- | --- |
| 数据与状态 | `$plugin->database()`、`schema()`、`settings()`、`session()` |
| 扩展与组合 | `$plugin->events()`、`filters()`、`slots()`、`services()` |
| 任务与基础 | `$plugin->jobs()`、`httpClient()`、`logger()` |
| 视图与资源 | `$plugin->views()`、`assets()`、`media()`、`uploads()` |
| 核心只读/业务查询 | `$plugin->products()`、`posts()`、`categories()`、`inquiries()`、`messages()` |

---

## 3. 发布前必跑验证

```bash
php shopagg plugin:validate <plugin-dir>
php shopagg plugin:test <plugin-dir>
php shopagg plugin:pack <plugin-dir>
```
