# Testing Guide — ServiceHub

Complete walkthrough for testing every flow, with credentials and example payloads.

---

## Start the Server

```bash
php artisan serve
# Server running at: http://127.0.0.1:8000
```

---

## Test Credentials

| Role | Email | Password | Notes |
|---|---|---|---|
| **User** | `test@example.com` | `password` | General customer account |
| **Provider** | `ali-ac-services@provider.test` | `password` | Ali AC Services · Gulshan · ⭐ 4.8 |
| **Provider** | `cool-breeze-hvac@provider.test` | `password` | Cool Breeze HVAC · North Karachi |
| **Provider** | `frostmaster-ac@provider.test` | `password` | FrostMaster AC · Clifton · ⭐ 4.9 |

> **Admin account:** No admin is seeded by default. To create one, run:
> ```bash
> php artisan tinker --execute 'App\Models\User::where("email","test@example.com")->update(["role"=>"admin"]);'
> ```
> Then log in with `test@example.com` / `password`.

---

## Web UI Testing

### Flow 1 — User books a service

1. Open `http://localhost:8000` — redirects to `/login`
2. Log in as `test@example.com` / `password`
3. Click **New Request** in the sidebar
4. Enter a service description (examples below) and click **Parse & Find Providers**

   | Language | Example input |
   |---|---|
   | Roman Urdu | `Mujhe kal subah Gulshan mein AC technician chahiye` |
   | Urdu | `مجھے آج ڈی ایچ اے میں پلمبر چاہیے` |
   | English | `I need an electrician in PECHS urgently` |
   | Mixed | `AC repair needed urgently, Gulshan area, budget tight` |

5. Set latitude `24.9260` / longitude `67.0930` (Gulshan, Karachi default)
6. Agent 1 parses the request → you see ranked providers
7. Click **Get Quote** on your preferred provider
8. Choose a slot (tomorrow, any hour 8 AM–8 PM) and click **Confirm Booking**
9. On the booking detail page, click **Confirm Booking** to move from `pending` → `confirmed`
10. View the receipt at the bottom of the booking detail

### Flow 2 — Submit feedback

1. Complete Flow 1 but change the booking status to `completed` via provider login:
   - Log out, log in as `ali-ac-services@provider.test` / `password`
   - Go to **My Bookings** → find the booking → click **En Route** → then **Complete**
2. Log back in as `test@example.com` / `password`
3. Open the booking → scroll to **Leave Feedback** → select a star rating and submit

### Flow 3 — Open a dispute

1. After a booking exists in any non-cancelled status:
   - Go to **My Bookings** → open the booking
   - Scroll to bottom → click **Open a dispute**
   - Select issue type: `Poor Quality`
   - Describe the issue and submit
2. The AI auto-generates a Stage 1 resolution offer (e.g. "20% refund or free re-service")
3. Go to **Disputes** in the sidebar to see the dispute status

### Flow 4 — Admin panel

1. First make your account admin (see credentials section above)
2. Log out and log back in → redirected to `/admin`
3. **Providers tab**: find `Rogue Services` → Actions → set to Blacklisted
4. **Bookings tab**: filter by status, see all bookings across all users
5. **Disputes tab**: see escalated disputes with human flag

### Flow 5 — Provider dashboard

1. Log in as `ali-ac-services@provider.test` / `password`
2. **Dashboard** shows today's bookings (if any)
3. **My Bookings** → filter by Confirmed → mark as En Route → mark as Complete
4. **My Profile** shows reputation score history

---

## API Testing (Postman / curl)

### Step 0 — Import API docs

- Open `http://localhost:8000/docs` in browser → interactive docs
- Download Postman collection: `http://localhost:8000/docs.postman`
- Import into Postman → set base URL `http://localhost:8000`

---

### Step 1 — Register or Login

**Register a new user:**
```http
POST http://localhost:8000/api/auth/register
Content-Type: application/json

{
    "name": "Ahmed Khan",
    "phone": "+92 300 1234567",
    "email": "ahmed@test.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Or login with existing user:**
```http
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{
    "email": "test@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "token": "1|abc123xyz...",
    "user": { "id": 1, "name": "Test User", "email": "test@example.com", "role": "user" }
}
```

> **Save the token.** Add `Authorization: Bearer {token}` to all subsequent requests.

---

### Step 2 — Submit a service request (Agent 1)

```http
POST http://localhost:8000/api/v1/service-requests
Authorization: Bearer {token}
Content-Type: application/json

{
    "input": "Mujhe kal subah Gulshan mein AC technician chahiye",
    "lat": 24.9260,
    "lng": 67.0930
}
```

**Response:**
```json
{
    "data": {
        "id": 1,
        "agent_run_id": "uuid-xxxx",
        "parsed_intent": {
            "service_type": "AC Technician",
            "location": "Gulshan-e-Iqbal",
            "urgency": "normal",
            "preferred_time": "tomorrow 08:00-12:00",
            "budget_sensitivity": "normal",
            "complexity": "basic",
            "detected_lang": "roman_urdu",
            "confidence": 0.94
        },
        "clarification_question": null
    }
}
```

> Save `data.id` as `SERVICE_REQUEST_ID` and `data.agent_run_id` as `RUN_ID`.

**If confidence < 0.75**, you get a clarification question instead — re-submit with more detail.

---

### Step 3 — Get ranked providers (Agent 2)

```http
GET http://localhost:8000/api/v1/providers?service_type=AC Technician&lat=24.9260&lng=67.0930&complexity=basic
Authorization: Bearer {token}
```

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Ali AC Services",
            "area": "Gulshan-e-Iqbal",
            "rating_avg": 4.8,
            "on_time_score": 94,
            "travel_time_min": 8,
            "match_score": 0.87,
            "distance_km": 2.1,
            "price_min": 1200,
            "reasoning": "top-rated (4.8/5), excellent on-time rate (94%), nearby"
        }
    ],
    "meta": { "complexity": "basic", "ranked_count": 3 }
}
```

> Save the top provider's `id` as `PROVIDER_ID`.

---

### Step 4 — Get a price quote (Agent 3)

```http
POST http://localhost:8000/api/v1/pricing/quote
Authorization: Bearer {token}
Content-Type: application/json

{
    "provider_id": {PROVIDER_ID},
    "service_request_id": {SERVICE_REQUEST_ID}
}
```

**Response:**
```json
{
    "data": {
        "standard": {
            "id": 1,
            "base_rate": 1200,
            "visit_fee": 150,
            "distance_cost": 63,
            "urgency_adj": 0,
            "surge_factor": 1.0,
            "loyalty_discount": 0,
            "total": 1413,
            "provider_net": 1201,
            "is_budget_tier": false
        },
        "budget_tier": null
    }
}
```

> Save `data.standard.id` as `QUOTE_ID`.

---

### Step 5 — Create booking (Agent 4)

```http
POST http://localhost:8000/api/v1/bookings
Authorization: Bearer {token}
Content-Type: application/json

{
    "provider_id": {PROVIDER_ID},
    "service_request_id": {SERVICE_REQUEST_ID},
    "pricing_quote_id": {QUOTE_ID},
    "slot_datetime": "2026-05-21T10:00:00"
}
```

**Response:**
```json
{
    "data": {
        "id": 1,
        "booking_ref": "BK-8X42M",
        "status": "pending",
        "conflict_check": "PASSED",
        "before_state": { "slot_1000": "OPEN" },
        "after_state": { "slot_1000": "RESERVED", "booking_ref": "BK-8X42M" }
    }
}
```

> Save `data.id` as `BOOKING_ID`.

---

### Step 6 — Confirm booking

```http
PUT http://localhost:8000/api/v1/bookings/{BOOKING_ID}/confirm
Authorization: Bearer {token}
```

**Response includes:** before/after state diff + receipt JSON + bilingual SMS payload.

---

### Step 7 — Schedule follow-up (Agent 5)

```http
POST http://localhost:8000/api/v1/bookings/{BOOKING_ID}/followup
Authorization: Bearer {token}
```

**Response:** 3 scheduled notification events with Urdu + English payloads.

---

### Step 8 — Update status

```http
PUT http://localhost:8000/api/v1/bookings/{BOOKING_ID}/status
Authorization: Bearer {token}
Content-Type: application/json

{ "status": "en_route" }
```

Then:
```http
{ "status": "completed" }
```

---

### Step 9 — Submit feedback

```http
POST http://localhost:8000/api/v1/bookings/{BOOKING_ID}/feedback
Authorization: Bearer {token}
Content-Type: application/json

{
    "rating": 4,
    "review": "Good service, arrived on time.",
    "photo_url": null
}
```

**Response includes** `reputation_update` with `score_before`, `score_after`, and `delta`.

---

### Step 10 — Open dispute (Agent 6)

```http
POST http://localhost:8000/api/v1/disputes
Authorization: Bearer {token}
Content-Type: application/json

{
    "booking_id": {BOOKING_ID},
    "trigger_type": "quality",
    "description": "AC still not cooling after the technician visited"
}
```

**Response:**
```json
{
    "data": {
        "dispute_id": 1,
        "stage": 1,
        "resolution_offer": "We can offer a 20% refund or a free re-service within 48 hours.",
        "options": ["accept_refund", "accept_reservice", "reject_escalate"],
        "expires_at": "2026-05-21T12:30:00"
    }
}
```

**Accept or escalate:**
```http
PUT http://localhost:8000/api/v1/disputes/{DISPUTE_ID}/resolve
Authorization: Bearer {token}
Content-Type: application/json

{ "action": "reject_escalate" }
```

Escalates to Stage 2. Repeat → Stage 3 sets `human_flag: true`.

---

### Step 11 — Export Antigravity trace

```http
GET http://localhost:8000/api/v1/agent-trace/{RUN_ID}/export
Authorization: Bearer {token}
```

Returns all 6 agent steps in sequence — paste this JSON into the Antigravity submission portal.

---

## Edge Cases to Test

| Scenario | How to trigger |
|---|---|
| Slot conflict | Book the same provider for the same slot twice → 409 Conflict |
| Low confidence parse | Send vague input: `"I need something done"` → clarification question returned |
| Surge pricing | Provider `Rogue Services` has `capacity_current = 0`, set it to 9 → surge factor 1.15x |
| Blacklisted provider | `Rogue Services` is already blacklisted → excluded from provider matching results |
| Loyalty discount | Make 3+ bookings with the same user → 5% loyalty discount applied on next quote |
| Budget tier | Set `budget_sensitivity = high` in a service request → budget alternative quote generated |
| Emergency urgency | Include "emergency" in request → `urgency_adj` = PKR 500 added to quote |

---

## Quick curl Test (no Postman)

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}' \
  | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

echo "Token: $TOKEN"

# 2. Submit request
curl -X POST http://localhost:8000/api/v1/service-requests \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"input":"I need a plumber in DHA urgently","lat":24.8118,"lng":67.0681}'
```

---

## Database Reset

To start fresh with clean seed data:

```bash
php artisan migrate:fresh --seed
```
