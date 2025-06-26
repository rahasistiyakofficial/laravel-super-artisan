# Super Artisan for Laravel

**Super Artisan** is a powerful Laravel package that enhances the Artisan console to streamline repetitive development tasks. The `make:super` command generates multiple files (e.g., models, controllers, migrations, views, or front-end components) in a single command, with support for custom paths and design patterns like MVC, repository, or service. The `run:workflow` command executes predefined sequences of Artisan commands for tasks like deployment.

## Features

- **Generate Multiple Files**: Create models, migrations, controllers, and views (Blade, Livewire, Vue, or React) with one command.
- **Optional Custom Paths**: Place files in default locations or specify custom subfolders for models, controllers, views, and migrations.
- **Design Patterns**: Support for MVC (default), repository (with interfaces and bindings), and service patterns.
- **Front-end Flexibility**: Generate Blade views (default), Livewire components, or Vue/React components.
- **Workflows**: Execute predefined sequences of Artisan commands.
- **Configurable**: Define custom blueprints and workflows in a configuration file.

## Requirements

- PHP >= 8.0
- Laravel 11.x or 12.x

## Installation

Install the package via Composer:

```bash
composer require rahasistiyak/super-artisan
```

Publish the configuration and stub files:

```bash
php artisan vendor:publish --tag=super-artisan-config
php artisan vendor:publish --tag=super-artisan-stubs
```

This creates:
- `config/super-artisan.php`: Configuration for blueprints and workflows.
- `stubs/vendor/super-artisan/`: Customizable stub files.

## Usage

### `make:super` Command

The `make:super` command generates files for a resource (e.g., `Post`) with customizable paths and patterns. By default, it uses standard Laravel directories, but you can optionally specify custom paths.

**Syntax**:

```bash
php artisan make:super {name} [--livewire|--vue|--react] [--path={path}] [--controller_path={path}] [--model_path={path}] [--view_path={path}] [--migration_path={path}] [--pattern=repository|service]
```

**Options**:

- `--livewire`: Generate a Livewire component (in `app/Livewire` and `resources/views/livewire`).
- `--vue`: Generate a Vue component (in `resources/js`).
- `--react`: Generate a React component (in `resources/js`).
- `--path={path}`: (Optional) Base path for controllers and views/components (e.g., `Admin` places controllers in `app/Http/Controllers/Admin` and views in `resources/views/Admin` or `resources/js/Admin`).
- `--controller_path={path}`: (Optional) Subfolder within `app/Http/Controllers` (e.g., `Admin` places controllers in `app/Http/Controllers/Admin`).
- `--model_path={path}`: (Optional) Subfolder within `app/Models` (e.g., `Custom` creates `app/Models/Custom/{name}.php`).
- `--view_path={path}`: (Optional) Subfolder within `resources/views` (for Blade/Livewire) or `resources/js` (for Vue/React).
- `--migration_path={path}`: (Optional) Subfolder within `database/migrations`.
- `--pattern=repository|service`: (Optional) Generate files for repository (with interface and controller injection) or service pattern.

**Note**: Only one of `--livewire`, `--vue`, or `--react` can be used. Specifying multiple will result in an error.

**Examples**:

1. **Default MVC with Blade Views**:

   ```bash
   php artisan make:super Post
   ```

   Generates:
   - Model: `app/Models/Post.php`
   - Migration: `database/migrations/*_create_posts_table.php`
   - Controller: `app/Http/Controllers/PostController.php`
   - Views: `resources/views/post/index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php`

2. **Livewire with Default Paths**:

   ```bash
   php artisan make:super Post --livewire
   ```

   Generates:
   - Model: `app/Models/Post.php`
   - Migration: `database/migrations/*_create_posts_table.php`
   - Controller: `app/Http/Controllers/PostController.php`
   - Livewire: `app/Livewire/Post.php`
   - View: `resources/views/livewire/post.blade.php`

3. **Livewire with Custom View Path**:

   ```bash
   php artisan make:super Post --livewire --view_path=admin/posts
   ```

   Generates:
   - Model: `app/Models/Post.php`
   - Migration: `database/migrations/*_create_posts_table.php`
   - Controller: `app/Http/Controllers/PostController.php`
   - Livewire: `app/Livewire/admin/posts/Post.php`
   - View: `resources/views/livewire/admin/posts/post.blade.php`

4. **Vue with Default Path**:

   ```bash
   php artisan make:super Post --vue
   ```

   Generates:
   - Model: `app/Models/Post.php`
   - Migration: `database/migrations/*_create_posts_table.php`
   - Controller: `app/Http/Controllers/PostController.php`
   - Vue: `resources/js/components/Post.vue`

5. **Vue with Custom Path**:

   ```bash
   php artisan make:super Post --vue --path=Admin
   ```

   Generates:
   - Model: `app/Models/Post.php`
   - Migration: `database/migrations/*_create_posts_table.php`
   - Controller: `app/Http/Controllers/Admin/PostController.php`
   - Vue: `resources/js/Admin/Post.vue`

6. **Repository Pattern with Custom Controller Path**:

   ```bash
   php artisan make:super Post --pattern=repository --controller_path=Admin
   ```

   Generates:
   - Model: `app/Models/Post.php`
   - Migration: `database/migrations/*_create_posts_table.php`
   - Controller: `app/Http/Controllers/Admin/PostController.php` (with repository injection)
   - Repository: `app/Repositories/PostRepository.php`
   - Interface: `app/Repositories/PostRepositoryInterface.php`
   - Binding: `app/Providers/RepositoryServiceProvider.php`
   - Views: `resources/views/post/index.blade.php`, etc.

7. **Custom Model Path**:

   ```bash
   php artisan make:super Post --model_path=Custom
   ```

   Generates:
   - Model: `app/Models/Custom/Post.php`
   - Migration: `database/migrations/*_create_posts_table.php`
   - Controller: `app/Http/Controllers/PostController.php`
   - Views: `resources/views/post/*.blade.php`

8. **Error on Multiple Front-end Options**:

   ```bash
   php artisan make:super Post --livewire --vue
   ```

   Output: `Only one of --livewire, --vue, or --react can be used.`

### `run:workflow` Command

Execute a sequence of Artisan commands defined in `config/super-artisan.php`.

**Syntax**:

```bash
php artisan run:workflow {workflow}
```

**Example**:

```bash
php artisan run:workflow deploy
```

Executes commands like `optimize:clear`, `migrate --force`, etc., as defined in the `deploy` workflow.

## Configuration

The `config/super-artisan.php` file allows you to define custom **blueprints** (file generation templates) and **workflows** (command sequences).

### Blueprints

Define sets of files to generate. Example:

```php
'blueprints' => [
    'crud' => [
        'description' => 'Generates a full CRUD stack.',
        'commands' => [
            'make:model {name} -m -f',
            'make:controller {name}Controller --resource --model={name}',
        ],
    ],
],
```

### Workflows

Define sequences of Artisan commands. Example:

```php
'workflows' => [
    'deploy' => [
        'description' => 'Clears caches and runs migrations.',
        'commands' => [
            'optimize:clear',
            'migrate --force',
            'route:cache',
            'view:cache',
        ],
    ],
],
```

## Customizing Stubs

Published stubs are located in `stubs/vendor/super-artisan/`. Modify these to customize generated files:

- `view_*.stub`: Blade view templates.
- `vue_component.stub`: Vue component template.
- `react_component.stub`: React component template.
- `repository.stub`: Repository class template.
- `repository_interface.stub`: Repository interface template.
- `repository_controller.stub`: Controller with repository injection.
- `service.stub`: Service class template.

## Repository Pattern

When using `--pattern=repository`, the package generates:
- A repository class (`app/Repositories/{name}Repository.php`).
- An interface (`app/Repositories/{name}RepositoryInterface.php`).
- A controller with dependency injection (`app/Http/Controllers/*/{name}Controller.php`).
- A binding in `app/Providers/RepositoryServiceProvider.php` to connect the interface to the repository.

This promotes loose coupling and maintainable code.

## Testing

Run tests to ensure the package works as expected:

```bash
vendor/bin/phpunit
```

Tests verify file generation, path handling, repository bindings, and error cases (e.g., multiple front-end options).

## Contributing

Contributions are welcome! Please submit pull requests or issues to the [GitHub repository](https://github.com/rahasistiyak/super-artisan).

## License

This package is licensed under the MIT License.

### **Contact**

For any questions or support, you can reach us at:
**Email**: [rahasistiyak.official@gmail.com](mailto:rahasistiyak.official@gmail.com)
