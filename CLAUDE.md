# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Start dev server (localhost:8000)
npm run dev

# Run tests
npm test

# Clear Symfony cache
cd project && php bin/console cache:clear

# Install PHP dependencies
cd project && composer install

# Inspect registered routes
cd project && php bin/console debug:router

# Regenerate static OpenAPI spec
cd project && php bin/console nelmio:apidoc:dump --format=json > public/doc/openapi.json
```

## Architecture

The repo has two layers at the root:

- `package.json` — npm script only; runs PHP's built-in server pointing at `project/public/`
- `project/` — the entire Symfony 8 application

### Symfony app (`project/`)

No database. Data is stored as JSON files in `project/db/`:
- `db/cars.json` — mutable, read/written at runtime
- `db/colours.json` — seeded with 4 colours (red, blue, white, black); treated as read-only reference data

**Request flow:** `public/index.php` → Symfony kernel → `src/Controller/` → `src/Repository/` (reads/writes JSON files)

**Service wiring:** `CarRepository` and `ColourRepository` receive `%kernel.project_dir%` as `$projectDir` constructor argument (defined explicitly in `config/services.yaml`). All other services use autowiring.

### Cars API endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/cars` | List all cars |
| GET | `/api/car/{id}` | Get single car |
| POST | `/api/cars` | Create car |
| DELETE | `/api/cars/{id}` | Delete car |

Validation rules enforced in `src/Validator/CarValidator.php`: `make` and `model` are required strings; `build_date` must be `Y-m-d` format and no older than 4 years; `colour_id` must match an entry in `colours.json`.

HTTP response codes: 200 (get/list), 201 (created), 204 (deleted), 404 (not found), 422 (validation failed).
