# Super Artisan for Laravel — v2.0

<p align="center">
  <strong>The ultimate Laravel scaffolding & workflow package.</strong><br>
  Generate complete resource stacks, run Artisan workflows, and supercharge your development speed.
</p>

<p align="center">
  <a href="https://packagist.org/packages/rahasistiyak/laravel-super-artisan"><img src="https://img.shields.io/packagist/v/rahasistiyak/laravel-super-artisan.svg" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/rahasistiyak/laravel-super-artisan"><img src="https://img.shields.io/packagist/php-v/rahasistiyak/laravel-super-artisan.svg" alt="PHP Version"></a>
  <a href="https://packagist.org/packages/rahasistiyak/laravel-super-artisan"><img src="https://img.shields.io/packagist/l/rahasistiyak/laravel-super-artisan.svg" alt="License"></a>
</p>

---

## What's New in v2.0

| Feature | v1 | v2 |
|---|---|---|
| `run:workflow` command | ❌ Missing | ✅ Fully implemented |
| Repository pattern (full triad) | ⚠️ Partial | ✅ Class + Interface + Provider |
| Bulk generation | ❌ | ✅ `make:super Post,Comment,Tag` |
| API scaffolding | ❌ | ✅ `--api` |
| Filament resource | ❌ | ✅ `--filament` |
| Domain-driven layout | ❌ | ✅ `--domain=Blog` |
| Blueprint system | ⚠️ Config only | ✅ Fully executable |
| `--force` / `--dry-run` | ❌ | ✅ Both supported |
| `make:super-request` | ❌ | ✅ New command |
| `make:super-policy` | ❌ | ✅ New command |
| `make:super-action` | ❌ | ✅ New command |
| `super:list` | ❌ | ✅ New DX helper |
| PHP & Laravel support | PHP 8.0+, L11-12 | PHP 8.2+, L11-12-13 |

---

## Requirements

- **PHP** >= 8.2
- **Laravel** 11.x, 12.x, or 13.x

---

## Installation

```bash
composer require rahasistiyak/laravel-super-artisan
```

Publish the config and stubs:

```bash
# Publish config (optional but recommended)
php artisan vendor:publish --tag=super-artisan-config

# Publish stubs (to customize templates)
php artisan vendor:publish --tag=super-artisan-stubs
```

This creates:
- `config/super-artisan.php` — Define your blueprints and workflows.
- `stubs/vendor/super-artisan/` — Customize every generated file template.

---

## Available Commands

| Command | Description |
|---|---|
| `make:super {name}` | Generate a complete resource stack |
| `make:repository {name}` | Generate a repository (class + interface + provider) |
| `make:service {name}` | Generate a service class |
| `make:super-request {name}` | Generate a FormRequest |
| `make:super-policy {name}` | Generate a Gate policy |
| `make:super-action {name}` | Generate a single-responsibility Action |
| `run:workflow {name}` | Execute a predefined Artisan command workflow |
| `super:list` | List all available workflows and blueprints |

---

## `make:super` — Resource Generator

### Signature

```bash
php artisan make:super {name} [options]
```

### Options

| Option | Description |
|---|---|
| `{name}` | Resource name(s). Comma-separate for bulk: `Post,Comment,Tag` |
| `--livewire` | Generate a Livewire component |
| `--vue` | Generate a Vue 3 component |
| `--react` | Generate a React component |
| `--api` | Generate an API resource controller (JSON responses) |
| `--filament` | Generate a Filament v3 resource |
| `--pattern=repository` | Apply the Repository pattern (class + interface + binding) |
| `--pattern=service` | Apply the Service pattern |
| `--blueprint={key}` | Run a named blueprint from config |
| `--domain={Name}` | Place files under a domain namespace |
| `--path={path}` | Base path for controllers and views |
| `--controller_path={path}` | Controller subfolder |
| `--model_path={path}` | Model subfolder |
| `--view_path={path}` | View/component subfolder |
| `--migration_path={path}` | Migration subfolder |
| `--force` | Overwrite existing files |
| `--dry-run` | Preview what would be generated without writing |
| `--interactive` | Prompt for options interactively |

### Examples

#### Basic CRUD (Blade Views)
```bash
php artisan make:super Post
```
Generates: Model, Migration, Factory, Controller, 4 Blade views

#### Bulk Generation
```bash
php artisan make:super Post,Comment,Tag
```
Generates the full stack for all three resources at once.

#### API Resource
```bash
php artisan make:super Post --api
```
Generates: Model, Migration, Factory, API Controller (JSON responses, no views)

#### Repository Pattern
```bash
php artisan make:super Post --pattern=repository
```
Generates: Model + Migration + Repository class + Interface + `RepositoryServiceProvider` binding + Controller with DI

#### Filament Resource
```bash
php artisan make:super Post --filament
```
Generates: Model + Migration + Filament v3 Resource (requires `filament/filament`)

#### Domain-Driven Layout
```bash
php artisan make:super Post --domain=Blog
```
Generates: `app/Http/Controllers/Blog/PostController.php`, views in `resources/views/Blog/post/`

#### Livewire
```bash
php artisan make:super Post --livewire
```

#### Vue / React
```bash
php artisan make:super Post --vue
php artisan make:super Post --react
```

#### Blueprint
```bash
php artisan make:super Post --blueprint=full-stack
```
Runs the `full-stack` blueprint: Model + Repository + Policy + FormRequests + Controller.

#### Dry Run
```bash
php artisan make:super Post --api --domain=Blog --dry-run
```
Preview exactly what would be created — nothing is written to disk.

#### Force Overwrite
```bash
php artisan make:super Post --force
```

---

## `run:workflow` — Workflow Runner

Execute predefined sequences of Artisan commands.

### Signature

```bash
php artisan run:workflow {workflow} [options]
```

### Options

| Option | Description |
|---|---|
| `--name={value}` | Substitute `{name}` token in workflow commands |
| `--dry-run` | Show the commands without running them |
| `--stop-on-failure` | Stop the workflow if any step fails |
| `--list` | List all available workflows |

### Built-in Workflows

| Workflow | Description |
|---|---|
| `deploy` | Clear caches, run migrations, rebuild caches, take app back up |
| `fresh` | Wipe and re-seed the database (dev only) |
| `optimize` | Rebuild all application caches |
| `clear` | Clear all caches |
| `test` | Run the test suite |
| `test-and-deploy` | Run tests then deploy (use `--stop-on-failure`) |
| `queue-restart` | Gracefully restart queue workers |
| `ide-helper` | Regenerate IDE helper files |

### Examples

```bash
# Standard deployment
php artisan run:workflow deploy

# Preview what deploy would do
php artisan run:workflow deploy --dry-run

# Test then deploy, stop if tests fail
php artisan run:workflow test-and-deploy --stop-on-failure

# Fresh database for local dev
php artisan run:workflow fresh

# List all workflows
php artisan run:workflow deploy --list
# or
php artisan super:list
```

---

## `make:repository` — Repository Generator

Generates the full repository triad:
1. **Repository class** (`app/Repositories/{Name}Repository.php`)
2. **Interface** (`app/Repositories/Contracts/{Name}RepositoryInterface.php`)
3. **Service Provider binding** (`app/Providers/RepositoryServiceProvider.php`)

```bash
php artisan make:repository PostRepository --model=Post
```

### Options

| Option | Description |
|---|---|
| `--model=` | Model to use (auto-inferred from name if omitted) |
| `--no-interface` | Skip generating the interface |
| `--no-binding` | Skip updating RepositoryServiceProvider |
| `--force` | Overwrite existing files |
| `--dry-run` | Preview output |

> **After generation**: Add `RepositoryServiceProvider::class` to `bootstrap/providers.php` (Laravel 11+).

---

## `make:service` — Service Generator

```bash
php artisan make:service PostService --model=Post
```

Generates: `app/Services/PostService.php` with typed methods and constructor injection.

---

## Additional Generators

### `make:super-request`

```bash
php artisan make:super-request StorePostRequest --rules=title:required,body:required|max:500
```

Generates: `app/Http/Requests/StorePostRequest.php` with pre-filled validation rules.

### `make:super-policy`

```bash
php artisan make:super-policy PostPolicy --model=Post
```

Generates: `app/Policies/PostPolicy.php` with all standard Gate methods (viewAny, view, create, update, delete, restore, forceDelete).

### `make:super-action`

```bash
php artisan make:super-action CreatePost --model=Post
```

Generates: `app/Actions/CreatePost.php` — a single-responsibility action class.

---

## `super:list` — Discovery Helper

```bash
# List all workflows
php artisan super:list

# List all blueprints
php artisan super:list --blueprints

# List everything
php artisan super:list --all
```

---

## Blueprints

Blueprints define reusable file-generation templates. Define your own in `config/super-artisan.php`.

### Built-in Blueprints

| Blueprint | Description |
|---|---|
| `crud` | Model + Migration + Factory + Resource Controller |
| `api-resource` | Model + Migration + Factory + API Controller |
| `repository-crud` | Full CRUD with Repository pattern |
| `livewire` | Model + Migration + Livewire component |
| `filament-resource` | Model + Migration + Filament resource |
| `full-stack` | Model + Repository + Policy + FormRequests + Controller |

### Custom Blueprint Example

```php
// config/super-artisan.php
'blueprints' => [
    'my-api' => [
        'description' => 'My custom API resource.',
        'commands' => [
            'make:model {name} -m -f',
            'make:super-request Store{name}Request',
            'make:super-request Update{name}Request',
            'make:super {name} --api',
        ],
    ],
],
```

```bash
php artisan make:super Product --blueprint=my-api
```

---

## Custom Workflows

```php
// config/super-artisan.php
'workflows' => [
    'my-deploy' => [
        'description' => 'My custom deployment steps.',
        'commands' => [
            'down',
            'migrate --force',
            'db:seed --class=ProductionSeeder',
            'optimize',
            'up',
        ],
    ],
],
```

```bash
php artisan run:workflow my-deploy --stop-on-failure
```

---

## Customizing Stubs

After publishing, edit stubs in `stubs/vendor/super-artisan/`:

| Stub | Purpose |
|---|---|
| `view_index.stub` | Blade index view |
| `view_create.stub` | Blade create view |
| `view_edit.stub` | Blade edit view |
| `view_show.stub` | Blade show/detail view |
| `vue_component.stub` | Vue 3 SFC component |
| `react_component.stub` | React functional component |
| `repository.stub` | Repository class |
| `repository_interface.stub` | Repository interface |
| `repository_controller.stub` | Controller with repository DI |
| `api_controller.stub` | API resource controller |
| `service.stub` | Service class |
| `action.stub` | Action class |
| `policy.stub` | Gate policy |
| `request.stub` | FormRequest |
| `filament_resource.stub` | Filament v3 Resource |

### Available Tokens

| Token | Example Output |
|---|---|
| `{{ name }}` | `Post` |
| `{{ lower_name }}` | `post` |
| `{{ plural }}` | `Posts` |
| `{{ lower_plural }}` | `posts` |
| `{{ snake_name }}` | `post` |
| `{{ snake_plural }}` | `posts` |
| `{{ kebab_name }}` | `post` |
| `{{ ns_prefix }}` | `\Admin` (or empty) |

---

## Repository Pattern — How It Works

When using `--pattern=repository` or `make:repository`, the package generates:

1. **Repository class** — Implements `RepositoryInterface` from the package with `all()`, `find()`, `findOrFail()`, `create()`, `update()`, `delete()`, `paginate()`, `firstWhere()`, `count()`.

2. **Repository interface** — Extends the base `RahasIstiyak\SuperArtisan\Contracts\RepositoryInterface`. Add model-specific methods here.

3. **Service provider binding** — Binds the interface to the concrete class via IoC. Register it in `bootstrap/providers.php`:

```php
// bootstrap/providers.php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class, // 👈 Add this
];
```

4. **Controller with DI** — The generated controller receives the repository interface via constructor injection, promoting testability and loose coupling.

---

## Testing

```bash
vendor/bin/phpunit
```

Run tests with coverage:

```bash
vendor/bin/phpunit --coverage-text
```

---

## Contributing

Contributions are welcome! Please submit pull requests or issues to the [GitHub repository](https://github.com/rahasistiyakofficial/laravel-super-artisan).

Please follow the existing code style and write tests for any new features.

---

## License

This package is licensed under the **MIT License**.

---

## Contact

**Email**: [rahasistiyak.official@gmail.com](mailto:rahasistiyak.official@gmail.com)  
**GitHub**: [rahasistiyakofficial](https://github.com/rahasistiyakofficial)
