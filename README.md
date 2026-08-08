# ResumeAI

An AI-powered resume analysis platform. Upload a resume, run an analysis, and
get a score plus prioritised, specific feedback.

Laravel API, Vue 3 client, any OpenAI-compatible model provider.

---

## What it does

- Sign in with Google
- Upload a PDF or DOCX; the text is extracted in the background
- Run one of four analyses:
  - **ATS check** — how well tracking systems can parse and rank the resume
  - **Content review** — whether bullet points show impact
  - **Formatting check** — structure, spacing, headings, date consistency
  - **Job comparison** — match against a specific posting, with keyword gaps
- Read a score, severity-ranked fixes, strengths, weaknesses and next steps
- Export a PDF report (paid plans)
- Track past analyses per resume

---

## Requirements

- PHP 8.3+ and Composer
- Node 20+
- An OpenAI-compatible API key
- Google OAuth credentials
- Optionally Docker, and Stripe keys if you want paid plans

---

## Getting started

```bash
git clone <repository-url>
cd ResumeAI
```

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Fill in at minimum, in `backend/.env`:

| Variable | Why |
|---|---|
| `OPENAI_API_KEY` | Runs the analyses |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` | Sign-in |
| `FRONTEND_URL` | OAuth redirect, CORS allow-list, Stripe return URLs |

`.env.example` documents every variable, including the optional Stripe and S3
settings. Then:

```bash
php artisan migrate
php artisan serve
```

### Queue worker — required

Text extraction and analysis run as queued jobs. **Nothing will finish without
a worker running**: uploads stay on "Reading…" and analyses stay on "Queued".

```bash
cd backend
php artisan queue:work
```

Leave it running in its own terminal alongside `php artisan serve`.

> Do not set `QUEUE_CONNECTION=sync` to avoid this. That puts a model call of
> up to two minutes inside the web request and exhausts the worker pool under
> any real traffic.

### Frontend

```bash
cd frontend
npm install
cp .env.example .env      # only if your API is not on localhost:8000
npm run dev
```

| Service | URL |
|---|---|
| Frontend | http://localhost:5173 |
| API | http://localhost:8000 |

---

## Docker

```bash
docker compose up -d
docker compose exec app php artisan migrate
docker compose logs -f
```

The compose file provides PostgreSQL and Redis; point `DB_CONNECTION=pgsql`
and the Redis variables at them, and run the queue worker service.

---

## Testing

```bash
cd backend && php artisan test
cd frontend && npx vue-tsc --noEmit && npm run build
```

---

## Checking the model provider

```bash
cd backend
php artisan ai:status
```

Reports the endpoint, model, whether the key works, and remaining rate limit.

---

## Deploying

Before going live:

- `APP_DEBUG=false` — debug mode returns stack traces and config values to the client
- `APP_ENV=production`, and a generated `APP_KEY`
- `SESSION_SECURE_COOKIE=true` behind HTTPS
- `SESSION_DRIVER` and `CACHE_STORE` on `database` or `redis`, never `file`,
  so sessions are shared across app servers
- `SANCTUM_STATEFUL_DOMAINS` and `FRONTEND_URL` set to the real frontend origin
- A supervised queue worker (`php artisan queue:work --tries=3`)
- A Stripe webhook pointed at `POST /api/billing/webhook`, with the signing
  secret in `STRIPE_WEBHOOK_SECRET`

---

## Project structure

```
ResumeAI
├── backend/     Laravel API — controllers, jobs, services, migrations
├── frontend/    Vue app — pages, layout, components, stores
├── docker/      Docker configuration
└── docker-compose.yml
```

---

## License

MIT
