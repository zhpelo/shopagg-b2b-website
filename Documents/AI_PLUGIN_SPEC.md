# ShopAgg AI Plugin Spec 1.0

给 AI 的硬性规则：

1. 目标为 PHP 8.1、SQLite3、无 Composer 安装步骤；依赖必须打包进插件。
2. 从 `examples/plugins/hello-shopagg` 复制目录结构。
3. `plugin.json` 必须通过 `Documents/plugin.schema.json` 和 `php shopagg plugin:validate <dir>`。
4. 插件 ID 使用 `^[a-z0-9]+(?:-[a-z0-9]+)*$`，版本使用完整 SemVer。
5. 所有类使用插件自己的 PSR-4 命名空间，处理器写 `Class@method`。
6. 不编辑 `app/`、`themes/`、`index.php` 或核心迁移；数据表只能通过 `PluginSchema` 创建。
7. 路由处理器接收 `Request, PluginContext, ...路径参数`，返回 `Response|array|string`。
8. 页面修改使用 Filter/Slot；业务通知使用 Event；耗时操作使用 `ChunkedJobInterface`。
9. 会员、订单、支付、积分、通知等组合能力使用 `App\Plugins\Contracts`，通过 `provides_services` / `requires_services` 声明接口版本，不要自创不兼容的共享用户体系。
10. 后台写路由声明 `admin`、`permission:plugin.{id}.{permission}`、`csrf`。
11. 插件升级新增数据结构时新增迁移文件，永远不要修改已发布迁移。
12. 输出前执行：`php shopagg plugin:test <dir>`、`php shopagg plugin:pack <dir>`。

常用对象：

- `$plugin->database()/schema()/settings()/session()`
- `$plugin->events()/filters()/slots()/services()`
- `$plugin->jobs()/httpClient()/logger()`
- `$plugin->views()/assets()/media()/uploads()`
- `$plugin->products()/posts()/categories()/inquiries()/messages()`

完整说明：`Documents/插件开发指南.md`。
