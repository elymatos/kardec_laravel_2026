# CLAUDE.md

This file provides guidance to Claude Code when working with this repository.

---

## Stack

| Layer | Tech | Version |
|---|---|---|
| Runtime | PHP | 8.5.3 |
| Framework | Laravel | 12.49.0 |
| Database | kardec (custom engine) | — |
| Real-time | Laravel Reverb | 1.7.0 |
| Server | Laravel (built-in / Artisan serve) | — |
| MCP | laravel/mcp | 0.5.3 |
| Frontend | Tailwind CSS + Alpine.js | 3.4.3 / 3.13.10 |
| Testing | Pest | 3.8.5 |
| Linting | Laravel Pint | 1.27.0 |

---

## MCP Servers (Enabled in this project)

Two MCP servers are active: **laravel-boost** and **playwright**. Use them proactively.

### laravel-boost

The primary development companion. Use it instead of guessing.

- **`application-info`** — call at the start of each chat to get package versions, models, and DB engine.
- **`database-schema`** / **`database-query`** — always use these to inspect or query the DB. Never assume schema from code alone.
- **`tinker`** — execute PHP/Eloquent snippets to debug or verify behavior.
- **`last-error`** / **`read-log-entries`** — check logs before asking the user to reproduce errors.
- **`browser-logs`** — read frontend console errors/exceptions (only recent ones are useful).
- **`list-artisan-commands`** — check available options before running `php artisan make:*`.
- **`get-absolute-url`** — always use this when sharing a project URL with the user.
- **`search-docs`** — **critically important**. Search docs before making any code changes. Pass multiple broad queries; do not include package names in the query text.

### playwright

Use playwright MCP tools for browser-based UI testing and verification:

- Navigate, click, fill forms, take screenshots, and assert page state programmatically.
- Prefer `browser-snapshot` over `browser-take-screenshot` when inspecting DOM state.
- Use `browser-console-messages` and `browser-network-requests` to catch frontend/API issues during testing.
- Always close the browser when done (`browser-close`).

---

## PHP Rules

- Use PHP 8.5 features: readonly properties, enums, named arguments, fibers, first-class callables.
- Use constructor property promotion in all `__construct()` methods.
- Never allow empty `__construct()` with zero parameters.
- Always declare explicit return types on all methods and functions.
- Use appropriate type hints for all parameters (including union types and nullable `?Type`).
- Use curly braces for all control structures, even single-line bodies.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for genuinely complex logic.
- Add array shape type definitions in PHPDoc when the array structure is meaningful.
- Enum keys must be TitleCase (e.g., `ActiveUser`, `PendingReview`).

---

## Laravel Rules

### Structure (Laravel 12 Streamlined)

- No `app/Http/Middleware/` — register middleware in `bootstrap/app.php`.
- No `app/Console/Kernel.php` — use `bootstrap/app.php` or `routes/console.php`.
- Commands in `app/Console/Commands/` auto-register; no manual registration needed.
- `bootstrap/providers.php` for application service providers.

### General

- Use `php artisan make:` commands for all new files (controllers, models, migrations, etc.). Pass `--no-interaction`.
- For generic PHP classes: `php artisan make:class`.
- Never use `env()` outside of config files. Always use `config('key')`.
- Prefer `Model::query()` over `DB::`. Leverage Eloquent fully.
- Prevent N+1 queries with eager loading (`with()`, `load()`).
- Use Webtool's Criteria query builder for very complex database operations.

### Models

- When creating a new model, also create a factory and seeder. Ask the user if they need anything else (check `list-artisan-commands` for available `make:model` options).

### Database

- Always use **laravel-boost MCP** to access the database (`database-schema`, `database-query`, `tinker`).

### Reverb / WebSockets

- Use Laravel Echo on the frontend (`laravel-echo` 1.16.1 is installed).
- Broadcasting config lives in `config/broadcasting.php`.

---

## Frontend

- **Tailwind CSS 3.4.3** — utility-first; do not write custom CSS unless unavoidable.
- **Alpine.js 3.13.10** — for interactivity; keep components small and scoped.
- Use the **`frontend-design` plugin** for high-quality, production-grade UI components. Invoke it via `/frontend-design` when building components or pages.
- If you get a Vite manifest error, run `yarn run build` or ask the user to run `yarn run dev` / `composer run dev`.

---

## Testing

- **Every change must be tested.** Write or update a Pest test and verify it passes before finishing.
- All tests are written with **Pest 3**. Use `php artisan make:test --pest {Name}`.
- Feature tests go in `tests/Feature/`, unit tests in `tests/Unit/`.
- Most tests should be feature tests.
- Use model factories for test data. Check for existing factory states before setting up models manually.
- Use `fake()` or `$this->faker` consistently with existing conventions in the file.
- Use datasets to reduce duplication in validation tests.
- Use `assertForbidden()`, `assertNotFound()`, `assertSuccessful()` etc. — not raw `assertStatus()`.
- Import `use function Pest\Laravel\mock;` explicitly when mocking.
- Do **not** delete or remove existing tests without user approval.

### Running Tests

```bash
# Run all tests
php artisan test

# Run a specific file
php artisan test tests/Feature/ExampleTest.php

# Filter by name
php artisan test --filter=testName
```

Run the minimal set of tests covering your change first, then ask the user if they want the full suite.

---

## Code Style

- Run `vendor/bin/pint --dirty` before finalizing any change.
- Never run `--test`; just fix with `vendor/bin/pint`.

---

## URLs

- Always use `mcp__laravel-boost__get-absolute-url` before sharing any project URL with the user.
