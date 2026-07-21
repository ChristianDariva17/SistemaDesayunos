# SistemaDesayunos

SistemaDesayunos is a Laravel 12 web application for managing products, customers, orders, inventory, and administrative reports. The interface is rendered with Blade and enhanced with Vite-managed JavaScript and CSS assets.

## Requirements

- PHP `^8.2` with the GD and PDO MySQL extensions
- Composer
- Node.js `^20.19.0` or `>=22.12.0`, with npm
- A MySQL-compatible database server

## Local Setup

Install the PHP and JavaScript dependencies:

```bash
composer install
npm install
```

Create `.env` from `.env.example`:

```bash
cp .env.example .env
```

On Windows PowerShell, use:

```powershell
Copy-Item .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Create the database configured by the `DB_*` values in `.env` (`sistemadariva` by default), then initialize its schema:

```bash
php artisan migrate
```

Build production assets with:

```bash
npm run build
```

For local development, the Composer development script starts the Laravel server, queue listener, application log stream, and Vite:

```bash
composer dev
```

## Product Image Runtime Requirements

Product thumbnails require PHP GD with JPEG, PNG, GIF, and WebP support in the web, CLI, and worker runtimes. New uploads generate their thumbnails automatically.

After deploying this requirement, generate thumbnails for existing product images before serving list traffic:

```bash
php artisan products:generate-thumbnails
```

## Quality Checks

```bash
composer test
composer analyse
vendor/bin/pint --test
npm run test:js
```

`composer analyse` runs Larastan/PHPStan against `app/` at level 5. The committed baseline contains the existing accepted findings; new findings must be fixed rather than added to it.
