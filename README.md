# Cars API

A REST API for managing cars built with PHP 8.4 and Symfony 8. No database — data is persisted in JSON files.

**Live demo:** https://gentlecodes-be.hopo.online

**Repository:** https://github.com/hopogc/backend-task

## Requirements

- PHP 8.4+
- Composer
- Node.js / npm

## Setup

```bash
# Install PHP dependencies
cd project && composer install && cd ..
```

## Running

```bash
npm run dev
```

Server starts at `http://localhost:8000`.

## API Endpoints

| Method   | Path             | Description      |
| -------- | ---------------- | ---------------- |
| `GET`    | `/api/cars`      | List all cars    |
| `GET`    | `/api/car/{id}`  | Get a single car |
| `POST`   | `/api/cars`      | Create a car     |
| `DELETE` | `/api/cars/{id}` | Delete a car     |

### Create a car — `POST /api/cars`

**Request body (JSON):**

```json
{
    "make": "Toyota",
    "model": "Yaris",
    "build_date": "2023-03-15",
    "colour_id": 1
}
```

**Validation rules:**

- `make` and `model` — required non-empty strings
- `build_date` — required, format `Y-m-d`, must not be older than 4 years
- `colour_id` — required integer, must be one of: `1` (red), `2` (blue), `3` (white), `4` (black)

### HTTP response codes

| Code | Meaning              |
| ---- | -------------------- |
| 200  | Success (list / get) |
| 201  | Car created          |
| 204  | Car deleted          |
| 404  | Car not found        |
| 422  | Validation failed    |

## API Documentation

Interactive Swagger UI is available at `http://localhost:8000/doc`.

The OpenAPI spec is available at:

- **Live:** `http://localhost:8000/doc/openapi.json`
- **Static file:** `project/public/doc/openapi.json`

To regenerate the static file:

```bash
cd project && php bin/console nelmio:apidoc:dump --format=json > public/doc/openapi.json
```

## Tests

```bash
npm test
```

## Project Structure

```
/
├── package.json          # npm scripts (dev, test)
├── README.md
└── project/              # Symfony 8 application
    ├── db/
    │   ├── cars.json         # car data (runtime)
    │   └── colours.json      # colour reference data (seeded)
    ├── src/
    │   ├── Controller/
    │   │   └── CarController.php
    │   ├── Repository/
    │   │   ├── CarRepository.php
    │   │   └── ColourRepository.php
    │   └── Validator/
    │       └── CarValidator.php
    ├── tests/
    │   └── Controller/
    │       └── CarControllerTest.php
    └── config/
```
