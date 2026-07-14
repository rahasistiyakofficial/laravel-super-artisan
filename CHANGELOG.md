# Changelog

All notable changes to `rahasistiyak/laravel-super-artisan` are documented here.

This project follows [Semantic Versioning](https://semver.org/) and [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

---

## [2.0.0] — 2026-07-14

### 🚀 Major Release — Complete Package Overhaul

This is a major release with significant new features, API improvements, and breaking changes from v1.x.

---

### Added

#### New Commands
- **`run:workflow`** — Fully implemented workflow runner. Executes predefined Artisan command sequences from config with step-by-step progress, elapsed timing, and pass/fail summary.
- **`super:list`** — DX helper to list all available workflows and blueprints.
- **`make:super-request`** — Generate a `FormRequest` class with optional inline rule definitions via `--rules=field:rule,...`.
- **`make:super-policy`** — Generate a Gate policy class with all standard authorization methods.
- **`make:super-action`** — Generate a single-responsibility Action class, with auto-detection of the model name from the action name.

#### New `make:super` Flags
- **`--api`** — Generate an API resource controller with JSON responses and proper HTTP status codes. No Blade views are generated in API mode.
- **`--filament`** — Generate a Filament v3 Resource (requires `filament/filament`).
- **`--blueprint={key}`** — Run a named blueprint from `config/super-artisan.php`.
- **`--domain={Name}`** — Wrap generated files under a domain namespace (e.g. `--domain=Blog` places controller in `app/Http/Controllers/Blog/`).
- **`--force`** — Overwrite existing files.
- **`--dry-run`** — Preview all files that would be generated without writing anything to disk.
- **`--interactive`** — Enter interactive mode (prompts for each option).
- **Bulk generation** — The `{name}` argument now accepts a comma-separated list: `make:super Post,Comment,Tag`.

#### New Repository Features
- `make:repository` now generates the full repository triad automatically:
  1. Repository class (implements interface, full CRUD methods)
  2. Repository interface (`app/Repositories/Contracts/`)
  3. `RepositoryServiceProvider.php` with the interface → class binding
- New flags: `--no-interface`, `--no-binding`, `--force`, `--dry-run`.
- The generated controller (when using `--pattern=repository`) now uses constructor injection of the repository interface.

#### New Stubs
- `repository_interface.stub` — Now fully implemented (was empty in v1).
- `repository_controller.stub` — Now fully implemented (was empty in v1).
- `api_controller.stub` — New. REST API controller returning JSON.
- `action.stub` — New. Single-responsibility Action class.
- `policy.stub` — New. Gate Policy with all standard methods.
- `request.stub` — New. FormRequest with authorize + rules.
- `filament_resource.stub` — New. Filament v3 Resource.

#### New Blueprints (in config)
- `api-resource` — Model + Migration + Factory + API Controller.
- `repository-crud` — Full CRUD with the Repository pattern.
- `filament-resource` — Model + Migration + Filament resource.
- `full-stack` — Model + Repository + Policy + FormRequests (Store & Update) + Controller.

#### New Workflows (in config)
- `fresh` — `migrate:fresh --seed` (local dev).
- `optimize` — Rebuild all caches.
- `clear` — Clear all caches.
- `test` — Run the test suite.
- `test-and-deploy` — Tests then deployment steps (use with `--stop-on-failure`).
- `queue-restart` — Restart queue workers.
- `ide-helper` — Regenerate IDE helper files.

#### New `run:workflow` Features
- `--dry-run` — Show commands without running them.
- `--stop-on-failure` — Halt workflow on the first failed step.
- `--name={value}` — Substitute `{name}` token in workflow commands.
- `--list` — List all available workflows from within the command.
- Per-step timing (milliseconds) and an overall pass/fail/skip summary.

#### Architecture
- New `src/Concerns/GeneratesFiles.php` trait — shared stub resolution, token replacement, and force/dry-run-aware file writing across all generator commands. Eliminates duplicated logic.
- `src/Contracts/RepositoryInterface.php` — Now fully implemented with `all()`, `find()`, `findOrFail()`, `findBy()`, `create()`, `update()`, `delete()`, `paginate()`, `firstWhere()`, `count()` signatures.

#### Tests
- New `tests/TestCase.php` — Orchestra Testbench base class with in-memory SQLite and auto-cleanup of generated files.
- `MakeSuperCommandTest` — Expanded with 10 test cases covering all new flags.
- `MakeRepositoryCommandTest` — New. Tests full triad generation and all flags.
- `RunWorkflowCommandTest` — New. Tests workflow execution, dry-run, unknown workflow error, and list commands.

#### Documentation
- Complete README rewrite with badges, What's New table, comprehensive command reference, options tables, examples for every feature, blueprint/workflow configuration guides, stub token reference, and repository pattern explanation.
- This CHANGELOG.

---

### Changed

- **`make:super`** — Massively refactored internally. Generation steps are now clean protected methods. Uses the `GeneratesFiles` trait.
- **`make:repository`** — Now generates the full triad instead of just the class file.
- **`make:service`** — Now uses the `GeneratesFiles` trait, supports `--model`, `--force`, `--dry-run`, and generates a richer service class with typed methods.
- **Blade view stubs** — All four views (`index`, `create`, `edit`, `show`) now generate complete, functional HTML with proper layout, form handling, validation display, flash message support, and delete confirmation.
- **`repository.stub`** — Now implements the full `RepositoryInterface` contract with all CRUD methods and uses PHP 8.2 constructor property promotion.
- **`service.stub`** — Now includes typed methods, PHPDoc, constructor injection, and PHP 8.2 constructor property promotion.
- **`vue_component.stub`** / **`react_component.stub`** — Unchanged (still simple starters, as Vue/React setups vary widely).
- **`config/super-artisan.php`** — Expanded from 2 blueprints + 1 workflow to 6 blueprints + 8 workflows with descriptions.
- **`composer.json`** — Version bumped to `2.0.0`. PHP requirement raised to `>=8.2`. Laravel support extended to include `^13.0`. PHPUnit updated to `^11.0`. Keywords and description enhanced.
- **`SuperArtisanServiceProvider`** — Registers all 8 commands using a typed `array $commands` property.

---

### Fixed

- `repository_interface.stub` was an empty file — now fully implemented.
- `repository_controller.stub` was an empty file — now fully implemented.
- `src/Contracts/RepositoryInterface.php` was an empty file — now fully implemented.
- `run:workflow` command was referenced in README and config but did not exist — now fully implemented.
- Repository pattern via `make:super --pattern=repository` only generated the class, not the interface or binding — now generates all three.
- Duplicate migration generated when calling `make:model -m` in `make:super` — fixed by passing migration options directly to `make:model` and skipping the separate `make:migration` call.

---

### Removed

- Direct call to `make:migration` as a separate step in `make:super` — model creation with `-m` handles migration, avoiding duplicates.

---

### Breaking Changes

- **PHP minimum raised** to `8.2` (from `8.0`). Union types and constructor property promotion are used throughout.
- **`make:service` signature changed**: `--model=` option added; `name` argument now strips `Service` suffix to infer model automatically.
- **`make:repository` output changed**: The command now generates three files instead of one. The `--no-interface` and `--no-binding` flags can restore v1 behavior.
- **Repository stub changed**: Generated repository now implements an interface and has a different constructor signature. Update any custom `repository.stub` files if you published them.
- **View stubs changed**: All four Blade stubs are substantially different. If you published them in v1 and customized them, do not re-publish — your customizations will be preserved.

---

## [1.0.0] — Initial Release

- `make:super` command with Blade, Livewire, Vue, React support.
- `make:repository` and `make:service` basic commands.
- Custom path options (`--path`, `--controller_path`, `--model_path`, `--view_path`, `--migration_path`).
- `--pattern=repository|service` support.
- Basic config and stubs.
