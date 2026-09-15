## Power Consumption Tracker

A self-hosted Symfony web application for tracking home electricity (meter) consumption.
Log meter readings over time and get dashboards, charts, and per-day insights on how much
power you use and how usage trends across the week.

The app is a personal/first-party tool — it supports English and Polish locales, a single
meter at a time with full history, and simple tag-based notes on readings.

---

### Features

- **Meter readings** — create, edit, and delete meter readings, each tied to a device (meter)
  and an optional set of tags.
- **Dashboard** — the latest readings with a quick summary (average, median, and total usage).
- **Analysis charts** — built with [Chart.js](https://www.chartjs.org) via Symfony UX Chart.js:
  - **Usage over time** — daily usage (bar) plus average hourly usage (line) across a date range.
  - **Candle / day-of-week view** — per day of the week, the min/max usage range (bar) with
    the average and median overlaid as lines.
- **Devices (meters)** — manage meters. A single device is marked **active** at a time
  (`isCurrent`), and new readings default to the active meter. Each device has a
  `useDecimal` flag that controls whether values are entered with or without a decimal place.
- **Tags** — free-form labels you can attach to readings and manage in Settings.
- **Accounts** — username/password login. New users get `ROLE_USER`; only `ROLE_ADMIN` can
  register additional accounts.
- **Localization** — English (`en`) and Polish (`pl`).

---

### Tech stack

| Layer | Technology |
|------|-----------|
| Language | PHP **8.4+** |
| Framework | **Symfony 7.4** |
| ORM / DB | Doctrine ORM **3**, DBAL **3**, migrations |
| Database | **PostgreSQL 13+** |
| Templating | Twig, Twig Components |
| Frontend | Webpack Encore, **Tailwind CSS**, Chart.js, Stimulus, Turbo |
| Security | Symfony Security (form login, role-hierarchy) |
| Quality | PHPUnit, PHPStan, PHP-CS-Fixer |

---

### Requirements

- PHP >= 8.4
- Composer
- Node.js (for building front-end assets)
- PostgreSQL 13+
- A reachable database for the configured user/credentials

---

### Installation

From the project root:

```bash
# 1. Install PHP dependencies
composer install

# 2. Install front-end dependencies
npm install

# 3. Copy the environment file and adjust values (see next section)
cp .env .env.local
```

> `.env` already ships a `DATABASE_URL` pointing at a `power` database on the `mysql`
> host (the Postgres service defined in `docker-compose-project.yml`). Adjust it in
> `.env.local` to match your host (e.g. `localhost:5432`), user, password, and database name.

### Environment variables

The values below live in `.env` / `.env.local` (never commit real secrets):

```dotenv
APP_ENV=dev
APP_SECRET="a-random-32-char-string"
DATABASE_URL="postgresql://user:password@host:5432/power?serverVersion=13&charset=utf8"
```

### Database

Point `DATABASE_URL` at a valid, existing PostgreSQL database named `power`, then run:

```bash
# Create the schema and apply migrations
php bin/console doctrine:migrations:migrate
```

Existing migrations live in `migrations/` (sequences, `devices`, `reading`, `tag`, `user`
tables, and the `devices.use_decimal` flag).

---

### Running the application

Build the front-end assets, then serve:

```bash
# Development (watch mode) — run in one terminal
npm run watch

# Serve the app — run in another terminal
php -S 127.0.0.1:8000 -t public
```

Then open http://127.0.0.1:8000

For a production asset build:

```bash
npm run build
```

---

### Main routes

All routes are locale-prefixed (`/en/...`, `/pl/...`).

| Route | Purpose | Access |
|-------|---------|--------|
| `/` or `/{_locale}` | Login page (redirects to dashboard when authenticated) | public |
| `/{_locale}/dashboard` | Recent readings + summary | `ROLE_USER` |
| `/{_locale}/reading` | Readings list (paginated) | `ROLE_USER` |
| `/{_locale}/reading/new` | Add a reading | `ROLE_USER` |
| `/{_locale}/reading/{id}/edit` | Edit a reading | `ROLE_USER` |
| `/{_locale}/reading/{id}/delete` | Delete a reading | `ROLE_USER` |
| `/{_locale}/analysis` | Usage-over-time chart (date range) | `ROLE_USER` |
| `/{_locale}/candle` | Day-of-week range/average/median chart | `ROLE_USER` |
| `/{_locale}/settings` | Manage tags and devices | `ROLE_USER` |
| `/{_locale}/device/new` | Add a meter | `ROLE_USER` |
| `/{_locale}/tag/new` | Add a tag | `ROLE_USER` |
| `/{_locale}/register` | Register a new user | `ROLE_ADMIN` |
| `/{_locale}/logout` | Log out | authenticated |

---

### Data model

- **`User`** — username, hashed password, and roles. Every user is guaranteed at least
  `ROLE_USER`; `ROLE_ADMIN` extends it (role hierarchy).
- **`Device`** — a meter. `isCurrent` marks the active meter (only one is active);
  `useDecimal` records how values are entered for that meter.
- **`Reading`** — a meter reading: `value` (an integer), `date`, and a many-to-many link to
  `Tag`. `value` is stored as an integer scaled by `Reading::DECIMAL_DIVISION` (`10`), so the
  true reading is `value / 10` — this preserves a decimal place. When a device's `useDecimal`
  flag is off, a new reading's entered value is multiplied by `10` on save to keep storage
  consistent.
- **`Tag`** — a named label (`name` + `slug`) that can be attached to any number of readings.

Usage between two consecutive readings on the same device is derived (difference of values)
rather than stored; the dashboard summary and analysis charts are computed from that delta
using window functions in `ReadingRepository`.

---

### Project structure

```
bin/            Symfony console + phpunit entrypoints
config/         Framework + package configuration (Symfony config)
  packages/     Per-bundle config (doctrine, security, twig, translation, ...)
migrations/     Doctrine migrations (PostgreSQL)
public/         Web root (index.php, built assets in build/)
src/
  Controller/   Dashboard, Reading, Device, Tag, Analysis, Settings, Main, Registration
  Entity/       User, Device, Reading, Tag
  Form/         Form types for readings, devices, tags, registration
  Model/        Lightweight DTOs (e.g. ReadingDate for query results)
  Repository/   Data access (raw SQL windows for the usage math)
  Twig/         Custom Twig extensions (elapsed-time formatting)
  Utils/        Paginator + factory
templates/      Twig templates (dashboard, readings, analysis, settings, auth, ...)
assets/         Styles (Tailwind), fonts, images, Stimulus controllers
translations/   messages.en.yaml, messages.pl.yaml (+ validator translations)
tests/          Unit + integration tests
webpack.config.js, tailwind.config.js, postcss.config.js   Front-end build config
```

---

### Testing & static analysis

```bash
# Run the test suite (uses the "test" environment)
php bin/phpunit

# Static analysis (PHPStan, level 6)
vendor/bin/phpstan analyse -c phpstan.dist.neon

# Code style
vendor/bin/php-cs-fixer fix
```

---

### Front-end build

Asset processing is driven by `package.json` scripts:

```bash
npm run dev          # dev build
npm run watch        # dev build + watch
npm run dev-server   # dev build + Encore dev server
npm run build        # production build
```

Styling uses Tailwind CSS; charts are rendered with Chart.js through the
`symfony/ux-chartjs` component.

---

### Deployment

- A production build of the app is committed under `deploy-power/` (built front-end assets
  plus templates and `public/`), and `.hat.yml` describes the deployment target
  (Symfony 7, PHP 8.4, Postgres).
- For a manual deploy: run `npm run build`, set `APP_ENV=prod`, run
  `composer install --no-dev --optimize-autoloader`, and point your web server at `public/`.

---

### License

This project is MIT licensed — see [LICENSE](LICENSE).
