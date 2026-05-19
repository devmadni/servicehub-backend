# Mobile Developer Integration Guide
## AI Service Orchestrator — Complete API Reference

> **Give this entire file to your AI agent.** It contains every breaking change, every removed endpoint, the full new flow with exact request/response shapes, and edge case handling.

---

## Base URL

```
https://ai-service.nobleqs.com/api
```

All authenticated endpoints require:
```
Authorization: Bearer {token}
Content-Type: application/json
```

---

## What Changed (Breaking Changes Summary)

### REMOVED — Stop calling these, they no longer exist

| Old Endpoint | Why Removed | Replace With |
|---|---|---|
| `POST /v1/service-requests` | Replaced by unified orchestrator | `POST /v1/orchestrate` |
| `GET /v1/providers` | Replaced by unified orchestrator | `POST /v1/orchestrate` |
| `POST /v1/pricing/quote` | Now runs automatically inside orchestrate | `POST /v1/orchestrate` |

### ADDED — New endpoints

| New Endpoint | Purpose |
|---|---|
| `POST /v1/orchestrate` | Single call: parse intent + match providers + generate pricing |

### CHANGED — Same endpoint, new behaviour

| Endpoint | What Changed |
|---|---|
| `POST /v1/bookings` | New optional field `auto_confirm: true` — creates + confirms + schedules follow-up in one call |

---

## New User Flow (2 API calls total)

```
OLD FLOW (6 calls):
  POST /v1/service-requests
  GET  /v1/providers?lat=&lng=&service_type=
  POST /v1/pricing/quote
  POST /v1/bookings
  PUT  /v1/bookings/{id}/confirm
  POST /v1/bookings/{id}/followup

NEW FLOW (2 calls):
  POST /v1/orchestrate          ← Step 1: user types request, get providers + pricing
  POST /v1/bookings             ← Step 2: user picks slot, everything else is automatic
```

---

## STEP 1 — Orchestrate

### `POST /v1/orchestrate`

Sends user's natural language input (Urdu, Roman Urdu, English, or mixed), returns parsed intent + 5 nearest matching providers with available time slots and pricing.

**Rate limit:** 10 requests per minute per user.

#### Request

```json
{
  "input": "Mujhe kal subah Gulshan mein AC technician chahiye",
  "lat": 24.9219,
  "lng": 67.0892
}
```

| Field | Type | Required | Description |
|---|---|---|---|
| `input` | string | Yes | User's service request in any language, max 1000 chars |
| `lat` | number | Yes | User's current latitude |
| `lng` | number | Yes | User's current longitude |

#### Response — Success (HTTP 201)

```json
{
  "success": true,
  "message": "Orchestration complete",
  "data": {
    "run_id": "952f73df-ad85-47d0-9ec6-e9321a8aae15",
    "service_request_id": 13,
    "intent": {
      "service_type": "AC Technician",
      "location": "Gulshan-e-Iqbal",
      "urgency": "normal",
      "preferred_time": "tomorrow morning",
      "budget_sensitivity": "normal",
      "issue_severity": "low",
      "complexity": "basic",
      "detected_lang": "roman_urdu",
      "confidence": 0.95
    },
    "providers": [
      {
        "id": 1,
        "name": "Ali AC Services",
        "area": "Gulshan-e-Iqbal",
        "distance_km": 0.6,
        "travel_time_min": 1,
        "rating_avg": 4.8,
        "on_time_score": 94,
        "cancel_rate": 3,
        "experience_years": 8,
        "specializations": ["Inverter", "Split AC", "Commercial"],
        "score": 0.7747,
        "reason": "Top-rated (4.8/5), excellent on-time rate (94%), low cancellation rate",
        "pricing_quote_id": 8,
        "available_slots": [
          { "datetime": "2026-05-20 10:00:00", "label": "Wed, May 20 10:00 AM" },
          { "datetime": "2026-05-20 12:00:00", "label": "Wed, May 20 12:00 PM" },
          { "datetime": "2026-05-20 14:00:00", "label": "Wed, May 20 2:00 PM" },
          { "datetime": "2026-05-20 16:00:00", "label": "Wed, May 20 4:00 PM" },
          { "datetime": "2026-05-21 08:00:00", "label": "Thu, May 21 8:00 AM" }
        ],
        "pricing": {
          "base_rate": 1200,
          "visit_fee": 150,
          "urgency_adj": 0,
          "surge_factor": 1.0,
          "loyalty_discount": 0,
          "estimated_total": 1350,
          "currency": "PKR"
        }
      }
    ],
    "clarification_question": null
  }
}
```

#### Response — Clarification Needed (HTTP 200)

When the user's input is too vague (confidence < 0.75), the system asks a follow-up question instead of proceeding.

```json
{
  "success": true,
  "message": "Clarification required",
  "data": {
    "run_id": "abc123...",
    "proceed": false,
    "clarification_question": "What type of service do you need and where in Karachi?"
  }
}
```

**How to handle:** Show the `clarification_question` to the user, let them re-type, and call `POST /v1/orchestrate` again with the new input.

#### Intent Fields Explained

| Field | Values | Notes |
|---|---|---|
| `service_type` | AC Technician, Plumber, Electrician, Cleaning Service, Tutor, Carpenter, Painter, Driver, Mechanic, Beautician | Use this to display the detected service |
| `urgency` | low, normal, high, emergency | Affects pricing (`urgency_adj`) |
| `detected_lang` | english, roman_urdu, urdu, mixed | For your analytics |
| `confidence` | 0.0 – 1.0 | Always ≥ 0.75 when proceed succeeds |

#### Provider Fields Explained

| Field | Notes |
|---|---|
| `pricing_quote_id` | Pass this to `POST /v1/bookings` — the formal quote is already saved |
| `available_slots` | Array of next 5 open slots (weekdays 8am–8pm, 2hr intervals). Use `datetime` for booking, `label` for display |
| `pricing.estimated_total` | Show this to user as the price before confirming |
| `pricing.urgency_adj` | +500 emergency / +200 high / -50 low / 0 normal |
| `pricing.surge_factor` | 1.15 when provider is near capacity, otherwise 1.0 |
| `pricing.loyalty_discount` | 5% off for users with 3+ bookings (auto-applied) |
| `score` | 0–1 AI match score (higher = better fit) |
| `distance_km` | Straight-line distance from user's coordinates |
| `travel_time_min` | Estimated travel time in minutes |

---

## STEP 2 — Create Booking

### `POST /v1/bookings`

Creates a booking for the provider and slot the user selected. Pass `auto_confirm: true` to confirm and schedule follow-ups in the same call.

#### Request

```json
{
  "service_request_id": 13,
  "provider_id": 1,
  "pricing_quote_id": 8,
  "slot_datetime": "2026-05-20 10:00:00",
  "auto_confirm": true
}
```

| Field | Type | Required | Description |
|---|---|---|---|
| `service_request_id` | integer | Yes | From `POST /v1/orchestrate` response |
| `provider_id` | integer | Yes | The provider the user selected |
| `pricing_quote_id` | integer | No | From the selected provider's `pricing_quote_id`. Strongly recommended — links the formal quote to the booking |
| `slot_datetime` | datetime | Yes | Use `datetime` value from `available_slots` exactly as returned. Must be a future datetime. |
| `auto_confirm` | boolean | No | Default `false`. Set to `true` to confirm + schedule follow-ups automatically |

#### Response — auto_confirm: true (HTTP 201)

```json
{
  "success": true,
  "message": "Booking created, confirmed, and follow-up scheduled",
  "data": {
    "id": 15,
    "booking_ref": "BK-DCYW2",
    "status": "confirmed",
    "confirmed_at": "2026-05-19T18:30:00.000000Z",
    "conflict_check": "PASSED",
    "before_state": { "slot_1000": "OPEN" },
    "after_state": { "status": "confirmed", "confirmed_at": "2026-05-19T18:30:00" },
    "sms_payload": {
      "english": "Booking confirmed! Ref: BK-DCYW2. Provider: Ali AC Services will arrive at 10:00 AM.",
      "urdu": "بکنگ تصدیق ہو گئی! حوالہ: BK-DCYW2۔ Ali AC Services 10:00 AM پر آئیں گے۔"
    },
    "receipt": {
      "booking_ref": "BK-DCYW2",
      "service": "AC Technician",
      "provider": "Ali AC Services",
      "slot": "2026-05-20 10:00:00",
      "status": "confirmed",
      "pricing": {
        "base_rate": 1200,
        "visit_fee": 150,
        "distance_cost": 18,
        "surge_factor": 1.0,
        "loyalty_discount": 0,
        "total": 1368,
        "provider_net": 1163
      },
      "customer": "Test User",
      "generated_at": "2026-05-19 18:30:00"
    },
    "followup_schedule": {
      "booking_ref": "BK-DCYW2",
      "schedule": [
        {
          "event": "reminder_1h",
          "trigger_at": "2026-05-20 09:00:00",
          "type": "reminder",
          "payload": {
            "english": "Reminder: Your AC Technician appointment with Ali AC Services is in 1 hour. Ref: BK-DCYW2",
            "urdu": "یاد دہانی: آپ کی AC Technician اپائنٹمنٹ 1 hour میں ہے۔ حوالہ: BK-DCYW2"
          }
        },
        {
          "event": "en_route_notification",
          "trigger_at": "2026-05-20 09:45:00",
          "type": "en_route",
          "payload": {
            "english": "Your provider Ali AC Services is on the way! They should arrive in ~15 minutes.",
            "urdu": "آپ کے Ali AC Services راستے میں ہیں! تقریباً 15 منٹ میں پہنچیں گے۔"
          }
        },
        {
          "event": "completion_checklist",
          "trigger_at": "2026-05-20 12:00:00",
          "type": "completion",
          "payload": {
            "checklist": {
              "service_completed": false,
              "customer_satisfied": false,
              "payment_collected": false,
              "photos_uploaded": false
            },
            "english": "Service completed? Please confirm and rate your experience.",
            "urdu": "کیا سروس مکمل ہو گئی؟ براہ کرم تصدیق کریں اور اپنا تجربہ ریٹ کریں۔"
          }
        }
      ]
    }
  }
}
```

#### Response — auto_confirm: false (HTTP 201)

When `auto_confirm` is omitted or `false`, the booking is created as `pending` and you must confirm separately.

```json
{
  "success": true,
  "message": "Booking created",
  "data": {
    "id": 15,
    "booking_ref": "BK-DCYW2",
    "status": "pending",
    "conflict_check": "PASSED",
    "before_state": { "slot_1000": "OPEN" },
    "after_state": { "slot_1000": "RESERVED", "booking_ref": "BK-DCYW2" }
  }
}
```

#### Response — Slot Conflict (HTTP 409)

```json
{
  "success": false,
  "message": "Slot unavailable — please choose another time."
}
```

**How to handle:** Show a slot picker again using the `available_slots` from the orchestrate response, or re-call `GET /v1/providers/{id}/availability` for fresh slots.

---

## Remaining Endpoints (Unchanged)

### Auth

```
POST /auth/register
POST /auth/login
POST /auth/logout         (authenticated)
GET  /auth/me             (authenticated)
```

#### Register
```json
{ "name": "Ahmed Ali", "email": "ahmed@example.com", "password": "secret", "phone": "03001234567" }
```

#### Login
```json
{ "email": "ahmed@example.com", "password": "secret" }
```
Returns `{ "token": "...", "user": { ... } }` — store the token for all subsequent requests.

---

### Bookings

```
GET    /v1/bookings                        List user's bookings (paginated)
GET    /v1/bookings/{id}                   Get single booking with full details
PUT    /v1/bookings/{id}/confirm           Confirm a pending booking (if not using auto_confirm)
DELETE /v1/bookings/{id}                   Cancel a booking
GET    /v1/bookings/{id}/receipt           Get printable receipt
PUT    /v1/bookings/{id}/status            Update status (en_route, completed)
POST   /v1/bookings/{id}/followup          Schedule follow-up events (if not using auto_confirm)
POST   /v1/bookings/{id}/feedback          Submit rating and review after completion
```

#### Confirm a pending booking (manual, if not using auto_confirm)
```
PUT /v1/bookings/{id}/confirm
```
No body required.

#### Cancel a booking
```json
{ "reason": "No longer needed" }
```

#### Submit feedback
```json
{
  "rating": 5,
  "review": "Excellent service, very professional",
  "photo_url": "https://example.com/photo.jpg"
}
```

#### Update status
```json
{ "status": "en_route" }
```
Valid values: `en_route`, `completed`, `cancelled`

---

### Provider Details

```
GET /v1/providers/{id}                    Get provider profile
GET /v1/providers/{id}/availability       Get fresh available slots (next 7 days)
```

#### Availability response
```json
{
  "data": {
    "slots": [
      "2026-05-20 08:00:00",
      "2026-05-20 10:00:00"
    ],
    "provider_id": 1
  }
}
```

---

### Disputes

```
GET  /v1/disputes             List user's disputes
POST /v1/disputes             Open a dispute
GET  /v1/disputes/{id}        Get dispute details
PUT  /v1/disputes/{id}/resolve  Resolve a dispute
```

#### Open a dispute
```json
{
  "booking_id": 15,
  "trigger_type": "no_show",
  "description": "Provider never showed up"
}
```
`trigger_type` values: `no_show`, `late`, `quality`, `price`, `damage`, `refund`

---

### Service Request History (read-only)

```
GET /v1/service-requests          List user's past service requests (paginated)
GET /v1/service-requests/{id}     Get a single service request
```

---

### Agent Trace (for debugging / demo)

```
GET /v1/agent-trace/{run_id}         Raw trace steps
GET /v1/agent-trace/{run_id}/export  Formatted trace for display
```

The `run_id` comes from `POST /v1/orchestrate` response. Shows all 5 AI reasoning steps:

```json
{
  "data": {
    "run_id": "952f73df-...",
    "steps": [
      { "step": 1, "agent": "intent",    "reasoning": "Intent parsed via Gemini function calling. Language: roman_urdu.", "confidence": 0.95, "duration": "2484ms" },
      { "step": 2, "agent": "matching",  "reasoning": "Providers ranked by 10-factor scoring. Top 3 returned with available slots.", "confidence": 0.95, "duration": "10ms" },
      { "step": 3, "agent": "pricing",   "reasoning": "Formal pricing quotes saved for 3 matched providers.", "confidence": 0.98, "duration": "16ms" },
      { "step": 4, "agent": "booking",   "reasoning": "Conflict check passed. Booking record created.", "confidence": 0.99, "duration": "0ms" },
      { "step": 5, "agent": "followup",  "reasoning": "Follow-up schedule created with 3 notification events in Urdu and English.", "confidence": 0.97, "duration": "0ms" }
    ]
  }
}
```

---

## Error Response Format

All errors follow the same shape:

```json
{
  "success": false,
  "message": "Description of the error",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

| HTTP Status | Meaning |
|---|---|
| 200 | Success (also used for clarification_required) |
| 201 | Created (booking, service request created) |
| 401 | Unauthenticated — missing or invalid token |
| 403 | Forbidden — trying to access another user's resource |
| 409 | Conflict — slot unavailable |
| 422 | Validation failed — check `errors` object |
| 429 | Rate limited — wait and retry |
| 500 | Server error — report to backend |

---

## Complete Implementation Checklist for AI Agent

Remove all calls to these endpoints:
- [ ] `POST /api/v1/service-requests` — deleted, use `/orchestrate`
- [ ] `GET /api/v1/providers` with query params — deleted, use `/orchestrate`
- [ ] `POST /api/v1/pricing/quote` — deleted, runs inside `/orchestrate`

Implement new flow:
- [ ] On user input submit: call `POST /v1/orchestrate` with `{input, lat, lng}`
- [ ] If response `data.proceed === false`: show `data.clarification_question` to user, let them re-type
- [ ] If success: store `data.run_id`, `data.service_request_id`, and the providers array
- [ ] Display provider list with name, distance, rating, reason, `pricing.estimated_total`, and first available slot label
- [ ] On provider + slot selection: call `POST /v1/bookings` with `{service_request_id, provider_id, pricing_quote_id, slot_datetime, auto_confirm: true}`
- [ ] On 409 conflict: show "Slot taken, please pick another time" and re-show slot picker
- [ ] On booking success: show `data.booking_ref`, `data.sms_payload.english` (or urdu based on user lang), and `data.receipt.pricing.total`
- [ ] Store `data.followup_schedule.schedule` events locally to trigger push notifications at `trigger_at` times

Optional (for demo):
- [ ] Add a "Show AI Reasoning" button that calls `GET /v1/agent-trace/{run_id}/export` and displays the 5 steps

---

## Supported Languages

The `input` field in `POST /v1/orchestrate` accepts all of:

| Language | Example Input |
|---|---|
| English | `"Emergency plumber needed in DHA now"` |
| Roman Urdu | `"Mujhe kal subah Gulshan mein AC technician chahiye"` |
| Urdu script | `"ابھی پلمبر چاہیے کلفٹن میں پانی لیک ہو رہا ہے"` |
| Mixed | `"AC repair karna hai in Clifton, urgent"` |

---

## Notes

- All authenticated endpoints require `Authorization: Bearer {token}` header
- Token is returned from `POST /auth/login` — store it securely (AsyncStorage / SecureStore)
- `slot_datetime` must be sent exactly as returned in `available_slots[].datetime` — do not reformat
- All prices are in **PKR (Pakistani Rupees)**
- Slots are weekdays only, 8:00 AM – 8:00 PM, 2-hour intervals
- The `pricing_quote_id` from the orchestrate response is valid for 24 hours
