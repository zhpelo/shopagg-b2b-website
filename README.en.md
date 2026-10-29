<div align="center">

<a href="https://shopagg.org/">
  <img src="assets/admin/images/shopagg-icon.webp" width="96" height="96" alt="SHOPAGG CMS Logo">
</a>

# SHOPAGG CMS

**An open-source B2B website system for Chinese manufacturers and trading companies**

Pure PHP + SQLite · Self-hosted · Extensible · MIT licensed

[Website](https://shopagg.org/) · [Live demo](https://demo.shopagg.org/) · [User guide](https://shopagg.org/usage) · [Developer docs](https://shopagg.org/developer) · [Releases](https://shopagg.org/releases)

[简体中文](README.md) · [English](README.en.md)

[![Website](https://img.shields.io/badge/Website-shopagg.org-0FA5FC?logo=googlechrome&logoColor=white)](https://shopagg.org/)
[![Latest release](https://img.shields.io/github/v/release/zhpelo/shopagg-b2b-website?display_name=tag&sort=semver)](https://shopagg.org/releases)
[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)](https://shopagg.org/api/compatibility.json)
[![SQLite](https://img.shields.io/badge/SQLite3-003B57?logo=sqlite&logoColor=white)](https://shopagg.org/developer/data-model)
[![License: MIT](https://img.shields.io/badge/License-MIT-22C55E.svg)](LICENSE)

</div>

---

## Overview

SHOPAGG CMS is an open-source website system built for B2B export businesses. It brings product presentation, content operations, customer inquiries, themes, and plugin extensions into one lightweight application. The core runs on PHP 8.1+ and SQLite3 with no Composer packages required at runtime, making it suitable for shared hosting, VPS deployments, and company-owned servers.

**SHOPAGG** (Chinese name: 思普艾格) is the main brand. The open-source application is named **SHOPAGG CMS**, or **SHOPAGG** for short. Self-host it or deploy it through the [SHOPAGG website building platform](https://www.shopagg.com/).

Start with these official resources:

- [SHOPAGG CMS website](https://shopagg.org/)
- [Complete product capability catalog](https://shopagg.org/capabilities)
- [Live demo](https://demo.shopagg.org/)
- [Current stable release and downloads](https://shopagg.org/releases)

## Core capabilities

| Area | Highlights | Learn more |
|---|---|---|
| Products and catalog | Categories, galleries, four pricing modes, multi-variant SKUs, soft deletion | [Product management](https://shopagg.org/usage/product-management) |
| Content and media | Posts, case studies, standalone pages, rich text, media library | [Content and appearance](https://shopagg.org/usage/content-and-appearance) |
| Inquiries and leads | Contact messages, product inquiries, workflow states, CSV export | [Inquiries and daily operations](https://shopagg.org/usage/inquiries) |
| Themes and appearance | Theme switching, configurable blocks, menus, sliders | [Theme development](https://shopagg.org/developer/themes) |
| Plugins and extensions | Routes, events, filters, services, jobs, admin pages | [Plugin development](https://shopagg.org/developer/plugins) |
| App Store | Browse, install, and update plugins and B2B website themes | [Settings and extensions](https://shopagg.org/usage/settings-and-extensions) |
| Operations and updates | GitHub Release updates, pre-update backups, migrations, logs | [Operations](https://shopagg.org/developer/operations) |
| Permissions and security | Staff permissions, CSRF protection, upload validation, security headers | [Security boundaries](https://shopagg.org/developer/security) |
| SEO and localization | SEO fields, Sitemap, Open Graph, 249 online translation languages | [Capability catalog](https://shopagg.org/capabilities) |

The official [product capability catalog](https://shopagg.org/capabilities) is the source of truth for supported features. Integrations can consume the same information through the [capabilities JSON endpoint](https://shopagg.org/api/capabilities.json).

## Preview

Visit [demo.shopagg.org](https://demo.shopagg.org/) to explore the public site. The [user guide](https://shopagg.org/usage) explains the admin workflows and daily operations.

### Frontend

<img alt="SHOPAGG CMS frontend" src="https://github.com/user-attachments/assets/2f9f289a-3ccf-48b5-a959-aaa3699cc9ce">

### Admin panel

| Dashboard | Product management |
|:---:|:---:|
| <img alt="SHOPAGG CMS dashboard" src="https://github.com/user-attachments/assets/b6400b0b-f345-4321-9c90-5925c1e6b790"> | <img alt="SHOPAGG CMS product management" src="https://github.com/user-attachments/assets/86918ca6-dd59-4fde-bacf-eef4dc668460"> |

| Media library | Settings |
|:---:|:---:|
| <img alt="SHOPAGG CMS media library" src="https://github.com/user-attachments/assets/18a252ad-de48-4a3f-8fee5833cc6e"> | <img alt="SHOPAGG CMS settings" src="https://github.com/user-attachments/assets/21f24c46-7716-47df-bdeb-0cf2228ba5fd"> |

## Quick start

### Requirements

| Item | Requirement |
|---|---|
| PHP | 8.1 or newer |
| PHP extensions | `sqlite3`, `mbstring`, `fileinfo` |
| Web server | Apache with `mod_rewrite`, or Nginx |
| Writable directories | `storage/`, `uploads/` |

Read the [compatibility JSON](https://shopagg.org/api/compatibility.json) for the latest machine-readable requirements. The [installation guide](https://shopagg.org/usage/getting-started) covers Apache, Nginx, and production configuration.

### Install

```bash
git clone https://github.com/zhpelo/shopagg-b2b-website.git
cd shopagg-b2b-website
chmod -R 755 storage uploads
```

Point the site document root at the project directory, then open the site in a browser. SHOPAGG CMS creates the SQLite database and runs pending migrations automatically.

The default admin entry point is `/admin/login`. Initial installations commonly use `admin` / `admin123`; change the password immediately after the first login.

Continue with the official guides:

1. [First login and initialization](https://shopagg.org/usage/getting-started#first-login)
2. [Products and pricing modes](https://shopagg.org/usage/product-management)
3. [Content, menus, sliders, and themes](https://shopagg.org/usage/content-and-appearance)
4. [Plugins, staff permissions, and program updates](https://shopagg.org/usage/settings-and-extensions)
5. [Launch checklist and troubleshooting](https://shopagg.org/usage/launch-and-faq)

## Technical design

- **Pure PHP MVC:** a single entry point with routing, controllers, models, and views.
- **SQLite3:** site data lives in `storage/site.db` by default and evolves through versioned migrations.
- **Theme system:** frontend themes live in `themes/{theme}`, with content blocks editable from the admin panel.
- **Plugin API 1.0:** a manifest-driven runtime with caching, isolated data, jobs, and service contracts.
- **Portable deployment:** runs on Apache, Nginx, and common PHP hosting without depending on a particular cloud provider.

Read more: [System architecture](https://shopagg.org/developer/architecture) · [Data model](https://shopagg.org/developer/data-model) · [Application development](https://shopagg.org/developer/application-development)

### Project structure

```text
shopagg-b2b-website/
├── index.php               # Application entry point
├── app/
│   ├── Controllers/        # Frontend and admin controllers
│   ├── Core/               # Routing, database, auth, migrations, and other core modules
│   ├── Models/             # SQLite data models
│   ├── Plugins/            # Plugin API 1.0 runtime
│   ├── Migrations/         # Core database migrations
│   ├── views/admin/        # Admin views
│   └── routes.php          # Route registration
├── themes/                 # Frontend themes
├── plugin-dev/             # Local plugin development
├── plugin-assets/          # Published plugin assets
├── storage/                # Database, cache, backups, and logs
├── uploads/                # User uploads
└── shopagg                 # Plugin development CLI
```

## Plugin and theme development

Use the Plugin API for standalone business features instead of modifying core files. The CLI can scaffold, validate, test, and package plugins:

```bash
php shopagg plugin:make my-plugin
php shopagg plugin:validate plugin-dev/my-plugin
php shopagg plugin:test plugin-dev/my-plugin
php shopagg plugin:pack plugin-dev/my-plugin
```

Plugins install into a fixed `storage/plugins/{plugin-id}/` directory. Versions remain in `plugin.json` and the database. Installing an existing plugin replaces its files and preserves its activation state; new plugins require manual activation. Same-version reinstalls are also allowed; the site does not retain historical installations for version switching. Published assets also use fixed directories with a `?ver=` cache parameter.

Temporary code, asset, and database backups are removed after a successful operation or recovery. Failed recovery retains its workspace and reports its path. Active updates run new migrations and synchronize scheduled jobs without invoking `activate()` again. Recovery covers exceptions during the operation, not every runtime error after installation. Run `plugin:test` in a separate CLI process for full class-contract validation.

An automatic migration converts legacy version trees, retaining the running version of active plugins and the installed version of inactive plugins. Obsolete version trees are removed only after migration succeeds. Back up `storage/site.db`, `storage/plugins/`, and `assets/plugins/` before upgrading. Existing historical database backups are left intact. Run `php tests/plugin_installation_smoke.php` to verify installation, updates, and recovery in an isolated temporary directory.

- [Complete plugin development guide](https://shopagg.org/developer/plugins)
- [Theme structure, block fields, and secure output](https://shopagg.org/developer/themes)
- [Controllers, models, migrations, and admin pages](https://shopagg.org/developer/application-development)
- [Security boundaries and release checks](https://shopagg.org/developer/security)

## Development and validation

Release archives already include compiled admin CSS. Node.js is only required when changing admin Tailwind classes or `assets/admin/style.css`:

```bash
npm ci
npm run build:admin-css
```

Run syntax checks for changed PHP files before submitting. If you modify the admin navigation, run the existing smoke test:

```bash
php tests/admin_navigation_smoke.php
```

See the [developer documentation](https://shopagg.org/developer) and [operations guide](https://shopagg.org/developer/operations) for coding conventions and release validation.

## Documentation and machine-readable resources

The official website publishes resources for operators, developers, search engines, integrations, and AI systems:

| Resource | URL |
|---|---|
| Official website | [shopagg.org](https://shopagg.org/) |
| User guide | [shopagg.org/usage](https://shopagg.org/usage) |
| Developer documentation | [shopagg.org/developer](https://shopagg.org/developer) |
| Product capabilities | [shopagg.org/capabilities](https://shopagg.org/capabilities) |
| Releases | [shopagg.org/releases](https://shopagg.org/releases) |
| AI project summary | [llms.txt](https://shopagg.org/llms.txt) |
| Full AI context | [llms-full.txt](https://shopagg.org/llms-full.txt) |
| Project JSON | [api/project.json](https://shopagg.org/api/project.json) |
| Capabilities JSON | [api/capabilities.json](https://shopagg.org/api/capabilities.json) |
| Documentation index JSON | [api/docs/index.json](https://shopagg.org/api/docs/index.json) |
| Latest release JSON | [api/releases/latest.json](https://shopagg.org/api/releases/latest.json) |
| Static API OpenAPI document | [api/openapi.json](https://shopagg.org/api/openapi.json) |

## Releases and updates

Stable builds are published through GitHub Releases. The admin panel can check for new versions, download update packages, and create backups before replacing files. The [official releases page](https://shopagg.org/releases) provides release history, downloads, and version notes.

Before updating a production site, read the [update, backup, and recovery guide](https://shopagg.org/developer/operations), then back up `storage/site.db`, `uploads/`, and any custom themes.

## Contributing

Issues and pull requests are welcome:

1. Fork the repository and create a branch from the latest code.
2. Keep each change focused and include appropriate validation.
3. Open a pull request describing the problem, behavior change, and validation performed.

Before contributing, read the [developer documentation](https://shopagg.org/developer) for the architecture, plugin boundaries, theme conventions, and security requirements.

## License

[MIT License](LICENSE) — free to use, modify, and distribute in personal and commercial projects.

---

<div align="center">

Visit the **[SHOPAGG CMS website](https://shopagg.org/)** to explore the product, read the documentation, or download the latest release.

</div>
EOF

wc -l README.en.md
git diff --stat
git diff --check
git status --short
