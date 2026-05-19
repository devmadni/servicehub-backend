# ServiceHub — AI Service Orchestrator

> Hackathon project · Laravel 12 · 6 AI Agents · Karachi, Pakistan

An AI-powered service marketplace that orchestrates the full lifecycle of a home service booking — from a natural language request in Urdu, Roman Urdu, or English, through provider matching, dynamic pricing, booking simulation, follow-up, and dispute resolution. Every step is powered by a dedicated AI agent and fully traced for the Antigravity submission.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.2 |
| Auth | Laravel Sanctum (token-based) |
| AI | Google Gemini 2.0 Flash |
| Maps | Google Maps Distance Matrix API |
| Frontend | Blade + Tailwind CSS v4 |
| Database | SQLite (dev) / MySQL (prod) |
| API Docs | Scribe (interactive + Postman + OpenAPI) |

---

## Architecture — 6-Agent Pipeline

```
User Input (Urdu / Roman Urdu / English)
        │
        ▼
┌──────────────────┐     ┌──────────────────────┐     ┌───────────────┐
│  Agent 1         │────▶│  Agent 2             │────▶│  Agent 3      │
│  Intent Parser   │     │  Provider Matching   │     │  Pricing      │
│  (Gemini API)    │     │  (10-factor score)   │     │  Engine       │
└──────────────────┘     └──────────────────────┘     └───────────────┘
                                                               │
        ┌──────────────────────────────────────────────────────┘
        ▼
┌──────────────────┐     ┌──────────────────────┐     ┌───────────────┐
│  Agent 4         │────▶│  Agent 5             │────▶│  Agent 6      │
│  Booking         │     │  Follow-up &         │     │  Dispute      │
│  Simulator       │     │  Quality             │     │  Resolution   │
└──────────────────┘     └──────────────────────┘     └───────────────┘
        │
        ▼
  AgentTraceService — logs all 6 steps under a shared run_id
```

Every agent call is persisted in `agent_traces` with input, output, reasoning, confidence score, and duration. Export a full trace via `GET /api/v1/agent-trace/{run_id}/export`.

---

## Database — 9 Tables

```
users ──────────────┬── providers ──── categories
                    │       │
                    │       ├── provider_reputations
                    │       │
                    ├── service_requests
                    │       │
                    │       ├── bookings ──── pricing_quotes
                    │       │       │
                    │       │       ├── disputes
                    │       │       │
                    │       │       └── agent_traces (run_id)
                    │       │
                    └───────┘
```

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/          ← 9 API controllers (mobile / Postman)
│   │   └── Web/          ← 4 web controllers (Blade UI)
│   ├── Requests/         ← Form validation classes
│   └── Resources/        ← API response transformers
├── Models/               ← 9 Eloquent models
├── Services/             ← 7 service classes (all business logic)
└── Exceptions/

resources/views/
├── layouts/              ← app.blade.php, guest.blade.php
├── components/           ← nav-item, status-badge
├── auth/                 ← login, register
├── user/                 ← dashboard, request, providers, quote, bookings/, disputes/
├── provider/             ← dashboard, bookings, profile
└── admin/                ← dashboard, providers, bookings, disputes

routes/
├── web.php               ← All Blade UI routes
└── api.php               ← All 31 REST API routes
```

---

## Services

| Service | Agent | Responsibility |
|---|---|---|
| `IntentParserService` | Agent 1 | Gemini API, language detection, complexity classification, confidence scoring |
| `ProviderMatchingService` | Agent 2 | 10-factor weighted scoring, Google Maps travel time, top-3 ranking with reasoning |
| `PricingEngine` | Agent 3 | Base + visit + distance + urgency + surge + loyalty discount, budget tier |
| `BookingSimulatorService` | Agent 4 | Slot conflict check (30-min buffer), booking ref, receipt builder, bilingual SMS payload |
| `FollowUpService` | Agent 5 | 3-event schedule (reminder, en-route, completion) in Urdu + English |
| `DisputeService` | Agent 6 | 3-stage escalation, auto resolution offers, blacklist logic |
| `AgentTraceService` | All | Logs every agent step, powers trace export endpoint |

---

## API — 31 Endpoints

All `/api/v1/*` routes require `Authorization: Bearer {token}`.

| Group | Endpoints |
|---|---|
| Auth | `POST /api/auth/register` `POST /api/auth/login` `POST /api/auth/logout` `GET /api/auth/me` |
| Service Requests | `POST /api/v1/service-requests` `GET /api/v1/service-requests` `GET /api/v1/service-requests/{id}` |
| Providers | `GET /api/v1/providers` `GET /api/v1/providers/{id}` `GET /api/v1/providers/{id}/availability` |
| Pricing | `POST /api/v1/pricing/quote` `GET /api/v1/pricing/quote/{id}` |
| Bookings | `POST /api/v1/bookings` `GET /api/v1/bookings` `GET /api/v1/bookings/{id}` `PUT /api/v1/bookings/{id}/confirm` `DELETE /api/v1/bookings/{id}` |
| Follow-up | `POST /api/v1/bookings/{id}/followup` `PUT /api/v1/bookings/{id}/status` `POST /api/v1/bookings/{id}/feedback` `GET /api/v1/bookings/{id}/receipt` |
| Disputes | `POST /api/v1/disputes` `GET /api/v1/disputes` `GET /api/v1/disputes/{id}` `PUT /api/v1/disputes/{id}/resolve` |
| Agent Traces | `GET /api/v1/agent-trace/{run_id}` `GET /api/v1/agent-trace/{run_id}/export` |
| Admin | `GET /api/v1/admin/providers` `PUT /api/v1/admin/providers/{id}` `PUT /api/v1/admin/providers/{id}/status` `GET /api/v1/admin/bookings` |

**Interactive docs, Postman collection, and OpenAPI spec:** `http://localhost:8000/docs`

---

## Web UI — 3 Role Dashboards

| Role | URL | Features |
|---|---|---|
| **User** | `/dashboard` | Submit AI request → pick provider → view quote → book → feedback → disputes |
| **Provider** | `/provider` | Today's schedule, mark en-route / complete, booking history, reputation profile |
| **Admin** | `/admin` | System stats, provider management (suspend / blacklist), all bookings, dispute monitor |

---

## Setup

### Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- SQLite (default) or MySQL

### Installation

```bash
# 1. Clone
git clone https://github.com/YOUR_USERNAME/ai-service-hackhathon.git
cd ai-service-hackhathon

# 2. Install dependencies
composer install
npm install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Add API keys to .env
#    GEMINI_API_KEY=your_key_here
#    GOOGLE_MAPS_KEY=your_key_here  (optional)

# 5. Database
php artisan migrate:fresh --seed

# 6. Build frontend
npm run build

# 7. Generate API docs
php artisan scribe:generate

# 8. Start server
php artisan serve
```

Open `http://localhost:8000` — redirects to login automatically.

### Environment Variables

```env
GEMINI_API_KEY=           # Required for AI intent parsing (Agent 1)
GEMINI_ENDPOINT=https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent
GOOGLE_MAPS_KEY=          # Optional — haversine fallback used if empty
```

---

## Seeded Data

After `php artisan migrate:fresh --seed`:

- **10 service categories** — AC, Plumber, Electrician, Cleaning, Tutor, Carpenter, Painter, Driver, Mechanic, Beautician
- **14 providers** across Karachi (Gulshan, DHA, PECHS, Clifton, North Karachi, Scheme 33, Malir…)
- **1 blacklisted provider** (`Rogue Services`) for dispute stress-testing
- **15 users** total (1 test user + 14 provider accounts)

---

## API Documentation

| Format | URL |
|---|---|
| Interactive HTML | `http://localhost:8000/docs` |
| Postman Collection | `http://localhost:8000/docs.postman` |
| OpenAPI YAML | `http://localhost:8000/docs.openapi` |

---

## Antigravity Trace Export

After completing a booking, export the full 6-agent trace:

```
GET /api/v1/agent-trace/{run_id}/export
Authorization: Bearer {token}
```

Returns all 6 agent steps with reasoning, confidence, input/output payloads, and durations — ready for the Antigravity submission portal.

---

## Running Tests

```bash
php artisan test --compact
```
