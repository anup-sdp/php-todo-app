# PHP Todo CRUD App (SQLite)

A minimal Todo app for learning PHP + SQLite, with two ways to use it:

1. **HTML app** — server-rendered pages (`index.php`, `add.php`, etc.)
2. **JSON API** — REST endpoints under `api/` for Postman, curl, or a separate frontend

Both share the same SQLite database (`todos.db`), created automatically on first run.

## Requirements

- PHP 7.4+ with the `pdo_sqlite` extension (bundled with PHP by default on most systems)
- No Composer, no external dependencies

## Setup

```bash
# from the todo-app folder
php -S localhost:8000
```

Open `http://localhost:8000` in a browser for the HTML app.
`todos.db` is created automatically the first time `db.php` runs.

## File overview

| File | Purpose |
|---|---|
| `db.php` | Opens the SQLite connection and creates the `todos` table if it doesn't exist. Included by every other file. |
| `index.php` | Lists all todos, has the "add" form. |
| `add.php` | Handles the add-todo form submission (`POST`). |
| `edit.php` | Shows an edit form (`GET`) and saves changes (`POST`). |
| `toggle.php` | Flips a todo's done/undone status (`POST`). |
| `delete.php` | Deletes a todo (`POST`). |
| `api/todos.php` | JSON REST API — full CRUD, see below. |

The HTML files (`index.php`, `add.php`, `toggle.php`, `delete.php`) use classic HTML forms: they read `$_POST`, then redirect back to `index.php`. They're meant to be used in a browser, not called from Postman.

The API file (`api/todos.php`) reads/writes JSON and returns proper HTTP status codes, so it's meant for Postman, curl, or a JS/mobile frontend.

## Database

SQLite table `todos`:

| Column | Type | Notes |
|---|---|---|
| `id` | INTEGER | primary key, auto-increment |
| `title` | TEXT | required |
| `done` | INTEGER | 0 or 1 |
| `created_at` | TEXT | set automatically on insert |

## API reference

Base URL: `http://localhost:8000/api/todos.php`

| Method | URL | Body (JSON) | Response |
|---|---|---|---|
| GET | `/api/todos.php` | — | `200` array of all todos |
| GET | `/api/todos.php?id=1` | — | `200` single todo, or `404` |
| POST | `/api/todos.php` | `{"title": "Buy milk"}` | `201` created todo, or `422` if title missing |
| PUT | `/api/todos.php?id=1` | `{"title": "...", "done": true}` | `200` updated todo (both fields optional), or `404` |
| DELETE | `/api/todos.php?id=1` | — | `200` confirmation, or `404` |

Errors return `{"error": "..."}` with an appropriate status code (`400`, `404`, `422`, `405`).

CORS is open (`Access-Control-Allow-Origin: *`) so any frontend can call it directly — fine for local learning, but you'd lock this down before deploying anywhere public.

### Examples (curl)

```bash
# Create
curl -X POST http://localhost:8000/api/todos.php \
  -d '{"title":"Learn PHP"}'

# List all
curl http://localhost:8000/api/todos.php

# Get one
curl http://localhost:8000/api/todos.php?id=1

# Update (mark done)
curl -X PUT "http://localhost:8000/api/todos.php?id=1" \
  -d '{"done":true}'

# Delete
curl -X DELETE "http://localhost:8000/api/todos.php?id=1"
```

## Notes on security

This is a learning project, so a few things are intentionally simple:

- No authentication — anyone who can reach the server can read/write todos.
- `htmlspecialchars()` is used when rendering user input in HTML, to prevent XSS.
- All SQL uses prepared statements (`PDO` with bound parameters), to prevent SQL injection.
- CORS is wide open (`*`) — fine locally, tighten this for any real deployment.

## Next steps to extend this

- Add authentication (sessions or API tokens)
- Add pagination for the `GET` list endpoint
- Add due dates / priorities as new columns
- Add validation error details per field
- Split `api/todos.php` into route-per-file if it grows