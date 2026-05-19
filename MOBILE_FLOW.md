# ServiceHub — Mobile Developer Flow Guide

**Base URL:** `http://localhost:8000/api`  
**Auth Header:** `Authorization: Bearer {token}` (required on all endpoints except register/login)  
**Content-Type:** `application/json`

---

## Overview

ServiceHub is an AI-powered home service booking platform. Users describe their problem in natural language (Urdu, Roman Urdu, or English) and the system parses intent, matches providers, generates pricing, creates bookings, handles follow-ups, and resolves disputes — all via AI agents backed by Google Gemini 2.0.

---

## Complete User Journey

```
Register / Login
      ↓
Submit Service Request (AI parses natural language)
      ↓
Browse Ranked Providers (AI scoring + Google Maps)
      ↓
Check Provider Availability
      ↓
Get Pricing Quote (AI + surge pricing)
      ↓
Create Booking
      ↓
Confirm Booking
      ↓
Schedule Follow-Up Reminders
      ↓
Provider Updates Status (en_route → completed)
      ↓
Submit Feedback & Rating
      ↓ (if issue)
Open Dispute → Resolve or Escalate
```

---

## Step-by-Step Flow with Endpoints

---

### STEP 1 — Authentication

**Register**
```
POST /api/auth/register
```
Send user details → receive `token` for all future requests.

**Login**
```
POST /api/auth/login
```
Existing users get a fresh bearer token.

**Get Current User**
```
GET /api/auth/me
Authorization: Bearer {token}
```

**Logout**
```
GET /api/auth/logout
Authorization: Bearer {token}
```

---

### STEP 2 — Submit Service Request

```
POST /api/v1/service-requests
Authorization: Bearer {token}
```

User types their problem in any language. AI (Gemini) parses intent, detects language, classifies complexity and urgency.

**Returns:** `service_request_id` + parsed intent + `agent_run_id` for tracing.

> Rate limit: **10 requests/minute** (AI parsing is expensive).

---

### STEP 3 — Get Ranked Providers

```
GET /api/v1/providers?lat={lat}&lng={lng}&service_type={type}&complexity={complexity}
Authorization: Bearer {token}
```

AI scores all active providers on 10 factors (distance, rating, on-time score, cancel rate, specializations, etc.) using Google Maps for travel times. Returns **top 3** ranked providers with scores and reasons.

---

### STEP 4 — Provider Details & Availability

**Full provider profile:**
```
GET /api/v1/providers/{provider_id}
Authorization: Bearer {token}
```

**Available time slots (next 7 days):**
```
GET /api/v1/providers/{provider_id}/availability
Authorization: Bearer {token}
```
Returns 2-hour slots (weekdays 8am–8pm) filtered against existing bookings.

---

### STEP 5 — Get Pricing Quote

```
POST /api/v1/pricing/quote
Authorization: Bearer {token}
```

AI calculates: `base_rate + visit_fee + distance_cost + urgency_adj × surge_factor − loyalty_discount`.  
If user has high budget sensitivity, a budget-tier quote (50% reduction) is also returned.

**Save the `pricing_quote_id`** — needed when creating booking.

**Retrieve a saved quote:**
```
GET /api/v1/pricing/quote/{quote_id}
Authorization: Bearer {token}
```

---

### STEP 6 — Create Booking

```
POST /api/v1/bookings
Authorization: Bearer {token}
```

System checks for slot conflicts (no overlap within 30 min), generates a unique `booking_ref` (e.g. `BK-ABC12345`), creates booking in `pending` status.

**Returns:** `booking_id`, `booking_ref`, conflict check result, slot state change.

---

### STEP 7 — Confirm Booking

```
PUT /api/v1/bookings/{booking_id}/confirm
Authorization: Bearer {token}
```

Moves booking from `pending` → `confirmed`. Sets `confirmed_at` timestamp.

---

### STEP 8 — Schedule Follow-Up Reminders

```
POST /api/v1/bookings/{booking_id}/followup
Authorization: Bearer {token}
```

AI generates 3 bilingual (Urdu + English) reminder events:
1. **Reminder** — "Your service is booked for tomorrow"
2. **En-route** — "Provider is 10 mins away"
3. **Completion** — "Service completed, please rate"

---

### STEP 9 — Provider Updates Status

```
PUT /api/v1/bookings/{booking_id}/status
Authorization: Bearer {token}
```

Provider marks booking as `en_route` then `completed`. On `completed`, `completed_at` is set.

**Booking status flow:**
```
pending → confirmed → en_route → completed
                              ↘ disputed
                              ↘ cancelled
```

---

### STEP 10 — User Submits Feedback

```
POST /api/v1/bookings/{booking_id}/feedback
Authorization: Bearer {token}
```

User rates the provider (1–5). System recalculates provider's `rating_avg`, `on_time_score`, and logs a `ProviderReputation` record. User's `booking_count` increments.

---

### STEP 11 — Dispute (if needed)

**Open dispute:**
```
POST /api/v1/disputes
Authorization: Bearer {token}
```

Trigger types: `no_show`, `late`, `quality`, `price`, `damage`, `refund`

AI opens a Stage 1 auto-resolution with a `resolution_offer`.

**View dispute:**
```
GET /api/v1/disputes/{dispute_id}
Authorization: Bearer {token}
```

**Respond to resolution offer:**
```
PUT /api/v1/disputes/{dispute_id}/resolve
Authorization: Bearer {token}
```

Action options: `accept_refund`, `accept_reservice`, `reject_escalate`

Dispute stages:
```
Stage 1 (auto) → Stage 2 (escalated) → Stage 3 (human review)
```

**List user's disputes:**
```
GET /api/v1/disputes
Authorization: Bearer {token}
```

---

### STEP 12 — Agent Trace (Audit Trail)

```
GET /api/v1/agent-trace/{run_id}
GET /api/v1/agent-trace/{run_id}/export
Authorization: Bearer {token}
```

Each AI step is logged. Use `agent_run_id` returned from service-requests or providers responses. Export returns full JSON trace for audit.

---

## Booking List & Detail

**User's bookings (paginated):**
```
GET /api/v1/bookings
Authorization: Bearer {token}
```

**Single booking details:**
```
GET /api/v1/bookings/{booking_id}
Authorization: Bearer {token}
```

**Booking receipt:**
```
GET /api/v1/bookings/{booking_id}/receipt
Authorization: Bearer {token}
```

**Cancel booking:**
```
DELETE /api/v1/bookings/{booking_id}
Authorization: Bearer {token}
```
Only allowed if booking is not `completed` or `disputed`.

---

## Standard Response Envelope

All API responses follow this structure:

```json
{
  "success": true,
  "message": "Success",
  "data": { }
}
```

**Paginated list responses** include:
```json
{
  "success": true,
  "message": "Success",
  "data": [ ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

**Error response:**
```json
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "errors": {
    "field": ["Error message"]
  }
}
```

---

## Auth & Rate Limits

| Rule | Limit |
|------|-------|
| General API | 60 requests/min |
| POST service-requests | 10 requests/min |
| Auth method | Sanctum bearer token |

---

## Endpoint Quick Reference

| # | Method | Endpoint | Purpose |
|---|--------|----------|---------|
| 1 | POST | `/api/auth/register` | Register user |
| 2 | POST | `/api/auth/login` | Login, get token |
| 3 | GET | `/api/auth/me` | Current user info |
| 4 | GET | `/api/auth/logout` | Revoke token |
| 5 | POST | `/api/v1/service-requests` | Submit request (AI parse) |
| 6 | GET | `/api/v1/service-requests` | List user's requests |
| 7 | GET | `/api/v1/service-requests/{id}` | Single request detail |
| 8 | GET | `/api/v1/providers` | Get ranked providers |
| 9 | GET | `/api/v1/providers/{id}` | Provider profile |
| 10 | GET | `/api/v1/providers/{id}/availability` | Available slots |
| 11 | POST | `/api/v1/pricing/quote` | Get price quote |
| 12 | GET | `/api/v1/pricing/quote/{id}` | Retrieve quote |
| 13 | POST | `/api/v1/bookings` | Create booking |
| 14 | GET | `/api/v1/bookings` | List bookings |
| 15 | GET | `/api/v1/bookings/{id}` | Booking details |
| 16 | PUT | `/api/v1/bookings/{id}/confirm` | Confirm booking |
| 17 | DELETE | `/api/v1/bookings/{id}` | Cancel booking |
| 18 | GET | `/api/v1/bookings/{id}/receipt` | Booking receipt |
| 19 | POST | `/api/v1/bookings/{id}/followup` | Schedule reminders |
| 20 | PUT | `/api/v1/bookings/{id}/status` | Update status |
| 21 | POST | `/api/v1/bookings/{id}/feedback` | Rate & review |
| 22 | GET | `/api/v1/disputes` | List disputes |
| 23 | POST | `/api/v1/disputes` | Open dispute |
| 24 | GET | `/api/v1/disputes/{id}` | Dispute details |
| 25 | PUT | `/api/v1/disputes/{id}/resolve` | Resolve dispute |
| 26 | GET | `/api/v1/agent-trace/{runId}` | Get trace steps |
| 27 | GET | `/api/v1/agent-trace/{runId}/export` | Export full trace |
