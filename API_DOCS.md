# ServiceHub — API Reference

**Base URL:** `https://ai-service.nobleqs.com/api`  
**Auth:** `Authorization: Bearer {token}` on all protected endpoints  
**Content-Type:** `application/json`

---

## Authentication

### Register

```
POST /api/auth/register
```

**Request**
```json
{
  "name": "Ali Hassan",
  "email": "ali@example.com",
  "phone": "03001234567",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

**Response `201`**
```json
{
  "success": true,
  "message": "Registered successfully",
  "data": {
    "token": "1|sdfhj3h4jk...",
    "user": {
      "id": 1,
      "name": "Ali Hassan",
      "email": "ali@example.com",
      "phone": "03001234567",
      "role": "user",
      "booking_count": 0
    }
  }
}
```

---

### Login

```
POST /api/auth/login
```

**Request**
```json
{
  "email": "ali@example.com",
  "password": "secret123"
}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Authenticated",
  "data": {
    "token": "2|abcdef12345...",
    "user": {
      "id": 1,
      "name": "Ali Hassan",
      "email": "ali@example.com",
      "role": "user",
      "booking_count": 3
    }
  }
}
```

**Error `401`**
```json
{
  "success": false,
  "message": "Invalid credentials",
  "data": null
}
```

---

### Get Current User

```
GET /api/auth/me
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 1,
    "name": "Ali Hassan",
    "email": "ali@example.com",
    "phone": "03001234567",
    "role": "user",
    "booking_count": 3
  }
}
```

---

### Logout

```
GET /api/auth/logout
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Logged out",
  "data": null
}
```

---

## Service Requests

### Submit Service Request

```
POST /api/v1/service-requests
Authorization: Bearer {token}
```

> Rate limit: 10 requests/min. AI (Gemini) parses the natural language input.

**Request**
```json
{
  "input": "AC ki cooling kum ho gayi hai, urgent fix chahiye",
  "lat": 24.8215,
  "lng": 67.0099
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `input` | string | Yes | User's request in any language (min 5, max 500 chars) |
| `lat` | numeric | Yes | User's latitude (-90 to 90) |
| `lng` | numeric | Yes | User's longitude (-180 to 180) |

**Response `201`**
```json
{
  "success": true,
  "message": "Service request created",
  "data": {
    "id": 1,
    "agent_run_id": "f3a1b2c4-...",
    "raw_input": "AC ki cooling kum ho gayi hai, urgent fix chahiye",
    "detected_lang": "roman_urdu",
    "service_type": "AC Repair",
    "location": "Gulshan",
    "urgency": "high",
    "budget_sensitivity": "normal",
    "complexity": "intermediate",
    "issue_severity": "medium",
    "confidence": 0.94,
    "clarification_asked": null,
    "requested_datetime": null
  }
}
```

**Detected language values:** `english`, `urdu`, `roman_urdu`  
**Urgency values:** `low`, `medium`, `high`, `emergency`  
**Budget sensitivity values:** `low`, `normal`, `high`  
**Complexity values:** `basic`, `intermediate`, `complex`  
**Issue severity values:** `minor`, `medium`, `major`, `critical`

---

### List Service Requests

```
GET /api/v1/service-requests
Authorization: Bearer {token}
```

**Response `200`** (paginated, 15 per page)
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "service_type": "AC Repair",
      "urgency": "high",
      "complexity": "intermediate",
      "detected_lang": "roman_urdu",
      "confidence": 0.94,
      "created_at": "2026-05-19T10:30:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 15,
    "total": 18,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "http://localhost:8000/api/v1/service-requests?page=1",
    "last": "http://localhost:8000/api/v1/service-requests?page=2",
    "prev": null,
    "next": "http://localhost:8000/api/v1/service-requests?page=2"
  }
}
```

---

### Get Single Service Request

```
GET /api/v1/service-requests/{id}
Authorization: Bearer {token}
```

**Response `200`** — returns full service request object (same fields as create response).

**Error `403`** — if request belongs to another user.

---

## Providers

### Get Ranked Providers

```
GET /api/v1/providers?lat={lat}&lng={lng}&service_type={type}&complexity={complexity}
Authorization: Bearer {token}
```

| Query Param | Type | Required | Description |
|-------------|------|----------|-------------|
| `lat` | numeric | Yes | User latitude |
| `lng` | numeric | Yes | User longitude |
| `service_type` | string | Yes | e.g. `AC Repair`, `Plumbing` |
| `complexity` | string | No | `basic`, `intermediate`, `complex` |

**Response `200`**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "providers": [
      {
        "id": 3,
        "name": "Ahmed AC Expert",
        "area": "Gulshan",
        "lat": 24.8200,
        "lng": 67.0110,
        "rating_avg": 4.9,
        "on_time_score": 97.5,
        "cancel_rate": 1.5,
        "experience_years": 8,
        "specializations": ["AC Repair", "Refrigeration"],
        "capacity_current": 2,
        "risk_score": 0.08,
        "price_min": 1500,
        "status": "active",
        "score": 95.5,
        "reason": "Top-rated specialist, 3 min away, low cancel rate",
        "category": {
          "id": 2,
          "name": "HVAC",
          "slug": "hvac"
        }
      }
    ],
    "meta": {
      "ranked_count": 14,
      "returned": 3,
      "agent_run_id": "f3a1b2c4-..."
    }
  }
}
```

---

### Get Provider Profile

```
GET /api/v1/providers/{provider_id}
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 3,
    "name": "Ahmed AC Expert",
    "area": "Gulshan",
    "lat": 24.8200,
    "lng": 67.0110,
    "rating_avg": 4.9,
    "on_time_score": 97.5,
    "cancel_rate": 1.5,
    "experience_years": 8,
    "specializations": ["AC Repair", "Refrigeration"],
    "capacity_current": 2,
    "risk_score": 0.08,
    "price_min": 1500,
    "status": "active",
    "warning_count": 0,
    "category": {
      "id": 2,
      "name": "HVAC",
      "slug": "hvac"
    },
    "recent_reputations": [
      {
        "score_before": 4.88,
        "score_after": 4.90,
        "delta": 0.02,
        "trigger": "positive_feedback",
        "created_at": "2026-05-18T14:00:00.000000Z"
      }
    ]
  }
}
```

**Provider status values:** `active`, `suspended`, `blacklisted`

---

### Get Provider Availability

```
GET /api/v1/providers/{provider_id}/availability
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "provider_id": 3,
    "slots": [
      {
        "start": "2026-05-20T08:00:00",
        "end": "2026-05-20T10:00:00",
        "available": true
      },
      {
        "start": "2026-05-20T10:00:00",
        "end": "2026-05-20T12:00:00",
        "available": false
      },
      {
        "start": "2026-05-20T12:00:00",
        "end": "2026-05-20T14:00:00",
        "available": true
      }
    ]
  }
}
```

Slots cover the next 7 days (weekdays), 8am–8pm, in 2-hour windows. Unavailable slots are booked (pending/confirmed/en_route).

---

## Pricing

### Generate Quote

```
POST /api/v1/pricing/quote
Authorization: Bearer {token}
```

**Request**
```json
{
  "provider_id": 3,
  "service_request_id": 1
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `provider_id` | integer | Yes | Must exist in providers table |
| `service_request_id` | integer | Yes | Must exist in service_requests table |

**Response `201`**
```json
{
  "success": true,
  "message": "Quote generated",
  "data": {
    "standard": {
      "id": 5,
      "base_rate": 1500,
      "visit_fee": 150,
      "distance_cost": 300,
      "urgency_adj": 200,
      "surge_factor": 1.2,
      "loyalty_discount": 100,
      "total": 3160,
      "provider_net": 2844,
      "is_budget_tier": false
    },
    "budget_tier": {
      "id": 6,
      "base_rate": 750,
      "visit_fee": 150,
      "distance_cost": 300,
      "urgency_adj": 100,
      "surge_factor": 1.0,
      "loyalty_discount": 0,
      "total": 1580,
      "provider_net": 1422,
      "is_budget_tier": true,
      "alt_quote_id": 5
    },
    "agent_run_id": "g4b2c3d5-..."
  }
}
```

> `budget_tier` is only returned when the service request has `budget_sensitivity: "high"`.  
> Save the `id` from `standard` or `budget_tier` as `pricing_quote_id` for booking creation.

---

### Get Quote

```
GET /api/v1/pricing/quote/{quote_id}
Authorization: Bearer {token}
```

**Response `200`** — returns the same quote object as above (single quote, not both tiers).

---

## Bookings

### Create Booking

```
POST /api/v1/bookings
Authorization: Bearer {token}
```

**Request**
```json
{
  "provider_id": 3,
  "service_request_id": 1,
  "pricing_quote_id": 5,
  "slot_datetime": "2026-05-22 14:00:00"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `provider_id` | integer | Yes | Provider to book |
| `service_request_id` | integer | Yes | Linked service request |
| `pricing_quote_id` | integer | No | Quote to attach (nullable) |
| `slot_datetime` | datetime | Yes | Must be in the future |

**Response `201`**
```json
{
  "success": true,
  "message": "Booking created",
  "data": {
    "id": 42,
    "booking_ref": "BK-ABC12345",
    "status": "pending",
    "conflict_check": "PASSED",
    "before_state": {
      "slot_1400": "OPEN"
    },
    "after_state": {
      "slot_1400": "RESERVED"
    },
    "slot_datetime": "2026-05-22T14:00:00",
    "slot_end_datetime": "2026-05-22T16:00:00",
    "simulated": true,
    "agent_run_id": "h5c3d4e6-..."
  }
}
```

**Error `409`** — slot conflict detected:
```json
{
  "success": false,
  "message": "Slot conflict: provider has a booking within 30 minutes",
  "data": null
}
```

---

### List Bookings

```
GET /api/v1/bookings
Authorization: Bearer {token}
```

**Response `200`** (paginated, 15 per page)
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 42,
      "booking_ref": "BK-ABC12345",
      "status": "confirmed",
      "complexity": "intermediate",
      "slot_datetime": "2026-05-22T14:00:00",
      "slot_end_datetime": "2026-05-22T16:00:00",
      "confirmed_at": "2026-05-19T10:45:00",
      "completed_at": null,
      "simulated": true,
      "provider": {
        "id": 3,
        "name": "Ahmed AC Expert",
        "area": "Gulshan",
        "rating_avg": 4.9
      },
      "service_request": {
        "id": 1,
        "service_type": "AC Repair",
        "urgency": "high"
      },
      "pricing_quote": {
        "id": 5,
        "total": 3160,
        "is_budget_tier": false
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 3
  }
}
```

**Booking status values:** `pending`, `confirmed`, `en_route`, `completed`, `disputed`, `cancelled`

---

### Get Booking Details

```
GET /api/v1/bookings/{booking_id}
Authorization: Bearer {token}
```

**Response `200`** — full booking with all relationships:
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 42,
    "booking_ref": "BK-ABC12345",
    "status": "confirmed",
    "complexity": "intermediate",
    "slot_datetime": "2026-05-22T14:00:00",
    "slot_end_datetime": "2026-05-22T16:00:00",
    "confirmed_at": "2026-05-19T10:45:00",
    "completed_at": null,
    "simulated": true,
    "provider": { },
    "service_request": { },
    "pricing_quote": { },
    "disputes": []
  }
}
```

**Error `403`** — booking belongs to another user.

---

### Confirm Booking

```
PUT /api/v1/bookings/{booking_id}/confirm
Authorization: Bearer {token}
```

No request body required.

**Response `200`**
```json
{
  "success": true,
  "message": "Booking confirmed",
  "data": {
    "id": 42,
    "booking_ref": "BK-ABC12345",
    "status": "confirmed",
    "confirmed_at": "2026-05-19T10:45:00"
  }
}
```

**Error `422`** — booking is not in `pending` status.

---

### Cancel Booking

```
DELETE /api/v1/bookings/{booking_id}
Authorization: Bearer {token}
```

Optional body:
```json
{
  "cancellation_reason": "Plans changed"
}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Booking cancelled",
  "data": null
}
```

**Error `422`** — cannot cancel a `completed` or `disputed` booking.

---

### Get Booking Receipt

```
GET /api/v1/bookings/{booking_id}/receipt
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "booking_ref": "BK-ABC12345",
    "provider": "Ahmed AC Expert",
    "service": "AC Repair",
    "slot": "2026-05-22 14:00 – 16:00",
    "pricing": {
      "base_rate": 1500,
      "visit_fee": 150,
      "distance_cost": 300,
      "urgency_adj": 200,
      "surge_factor": 1.2,
      "loyalty_discount": 100,
      "total": 3160
    },
    "status": "completed",
    "completed_at": "2026-05-22T15:50:00"
  }
}
```

---

### Schedule Follow-Up Reminders

```
POST /api/v1/bookings/{booking_id}/followup
Authorization: Bearer {token}
```

No request body required.

**Response `201`**
```json
{
  "success": true,
  "message": "Follow-up scheduled",
  "data": {
    "booking_ref": "BK-ABC12345",
    "events": [
      {
        "type": "reminder",
        "scheduled_at": "2026-05-21T14:00:00",
        "text_ur": "آپ کی سروس کل 2 بجے بک ہے",
        "text_en": "Your service is booked for tomorrow at 2 PM"
      },
      {
        "type": "enroute",
        "scheduled_at": "2026-05-22T13:50:00",
        "text_ur": "پرووائیڈر 10 منٹ میں پہنچ رہا ہے",
        "text_en": "Provider is 10 minutes away"
      },
      {
        "type": "completion",
        "scheduled_at": "2026-05-22T16:05:00",
        "text_ur": "سروس مکمل ہو گئی، براہ کرم ریٹنگ دیں",
        "text_en": "Service completed, please leave a rating"
      }
    ],
    "agent_run_id": "i6d4e5f7-..."
  }
}
```

---

### Update Booking Status

```
PUT /api/v1/bookings/{booking_id}/status
Authorization: Bearer {token}
```

> Used by the **provider** to update job progress.

**Request**
```json
{
  "status": "en_route"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `status` | string | Yes | `en_route` or `completed` |

**Response `200`**
```json
{
  "success": true,
  "message": "Booking status updated",
  "data": {
    "id": 42,
    "status": "en_route",
    "completed_at": null
  }
}
```

When `status` is `completed`, `completed_at` is set to the current timestamp.

---

### Submit Feedback

```
POST /api/v1/bookings/{booking_id}/feedback
Authorization: Bearer {token}
```

**Request**
```json
{
  "rating": 5,
  "review": "Excellent work, very professional and on time.",
  "photo_url": "https://example.com/photos/job_photo.jpg"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `rating` | integer | Yes | 1 to 5 |
| `review` | string | No | Text review |
| `photo_url` | string | No | URL to uploaded photo |

**Response `201`**
```json
{
  "success": true,
  "message": "Feedback submitted",
  "data": {
    "feedback_id": 42,
    "reputation_update": {
      "score_before": 4.88,
      "score_after": 4.90,
      "delta": 0.02
    }
  }
}
```

---

## Disputes

### Open Dispute

```
POST /api/v1/disputes
Authorization: Bearer {token}
```

**Request**
```json
{
  "booking_id": 42,
  "trigger_type": "late",
  "description": "The provider arrived 1.5 hours late without any notification."
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `booking_id` | integer | Yes | Must belong to the authenticated user |
| `trigger_type` | string | Yes | See values below |
| `description` | string | Yes | Min 10, max 2000 characters |

**Trigger type values:** `no_show`, `late`, `quality`, `price`, `damage`, `refund`

**Response `201`**
```json
{
  "success": true,
  "message": "Dispute opened",
  "data": {
    "dispute_id": 8,
    "booking_ref": "BK-ABC12345",
    "trigger_type": "late",
    "stage": 1,
    "resolution_offer": "50% refund (PKR 1,580) or free rebook within 7 days",
    "options": ["accept_refund", "accept_reservice", "reject_escalate"],
    "expires_at": "2026-05-21T10:45:00",
    "human_flag": false
  }
}
```

---

### List Disputes

```
GET /api/v1/disputes
Authorization: Bearer {token}
```

**Response `200`** (paginated, 15 per page)
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 8,
      "trigger_type": "late",
      "description": "The provider arrived 1.5 hours late...",
      "stage": 1,
      "resolution_offer": "50% refund...",
      "outcome": null,
      "human_flag": false,
      "refund_amount": null,
      "resolved_at": null,
      "booking": {
        "id": 42,
        "booking_ref": "BK-ABC12345",
        "status": "disputed"
      }
    }
  ],
  "meta": { }
}
```

**Outcome values:** `resolved`, `refunded`, `escalated`, `blacklisted`  
**Stage values:** `1` (auto), `2` (escalated), `3` (human review)

---

### Get Dispute Details

```
GET /api/v1/disputes/{dispute_id}
Authorization: Bearer {token}
```

**Response `200`** — full dispute object with booking relationship.

**Error `403`** — dispute belongs to another user.

---

### Resolve Dispute

```
PUT /api/v1/disputes/{dispute_id}/resolve
Authorization: Bearer {token}
```

**Request**
```json
{
  "action": "accept_refund"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `action` | string | Yes | `accept_refund`, `accept_reservice`, or `reject_escalate` |

**Response — accepted (`accept_refund` or `accept_reservice`) `200`**
```json
{
  "success": true,
  "message": "Dispute resolved",
  "data": {
    "dispute_id": 8,
    "outcome": "refunded",
    "refund_amount": 1580,
    "resolved_at": "2026-05-20T11:00:00"
  }
}
```

**Response — escalated (`reject_escalate`) `200`**
```json
{
  "success": true,
  "message": "Dispute escalated",
  "data": {
    "dispute_id": 8,
    "stage": 2,
    "human_flag": true,
    "outcome": "escalated"
  }
}
```

---

## Agent Trace

### Get Trace Steps

```
GET /api/v1/agent-trace/{run_id}
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "run_id": "f3a1b2c4-...",
    "steps": [
      {
        "sequence": 1,
        "agent_name": "intent",
        "confidence": 0.94,
        "duration_ms": 1250,
        "created_at": "2026-05-19T10:30:01.000000Z"
      }
    ]
  }
}
```

---

### Export Full Trace

```
GET /api/v1/agent-trace/{run_id}/export
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "run_id": "f3a1b2c4-...",
    "exported_at": "2026-05-19T12:00:00",
    "steps": [
      {
        "sequence": 1,
        "agent_name": "intent",
        "input_payload": {
          "input": "AC ki cooling kum ho gayi hai",
          "lat": 24.8215,
          "lng": 67.0099
        },
        "output_payload": {
          "service_type": "AC Repair",
          "urgency": "high",
          "complexity": "intermediate",
          "detected_lang": "roman_urdu",
          "confidence": 0.94
        },
        "reasoning": "Multi-factor intent detection via Gemini 2.0 Flash...",
        "confidence": 0.94,
        "duration_ms": 1250
      }
    ]
  }
}
```

---

## Common Error Codes

| HTTP Status | Meaning | When |
|-------------|---------|------|
| `200` | OK | Successful GET/PUT/DELETE |
| `201` | Created | Successful POST that creates a resource |
| `400` | Bad Request | Malformed request |
| `401` | Unauthorized | Missing or invalid token |
| `403` | Forbidden | Authenticated but accessing another user's resource |
| `404` | Not Found | Resource does not exist |
| `409` | Conflict | Slot conflict when creating booking |
| `422` | Unprocessable | Validation failed or invalid state transition |
| `429` | Too Many Requests | Rate limit exceeded |
| `500` | Server Error | Internal error (check logs) |

---

## Enum Reference

| Field | Values |
|-------|--------|
| `user.role` | `user`, `provider`, `admin` |
| `service_request.detected_lang` | `english`, `urdu`, `roman_urdu` |
| `service_request.urgency` | `low`, `medium`, `high`, `emergency` |
| `service_request.budget_sensitivity` | `low`, `normal`, `high` |
| `service_request.complexity` | `basic`, `intermediate`, `complex` |
| `service_request.issue_severity` | `minor`, `medium`, `major`, `critical` |
| `booking.status` | `pending`, `confirmed`, `en_route`, `completed`, `disputed`, `cancelled` |
| `provider.status` | `active`, `suspended`, `blacklisted` |
| `dispute.trigger_type` | `no_show`, `late`, `quality`, `price`, `damage`, `refund` |
| `dispute.outcome` | `resolved`, `refunded`, `escalated`, `blacklisted` |

---

## Flow — Which API Goes Where

| Screen | APIs Used |
|--------|-----------|
| Splash / Onboarding | `POST /auth/register` |
| Login | `POST /auth/login` |
| Home / Submit Request | `POST /v1/service-requests` |
| Provider List Screen | `GET /v1/providers?lat&lng&service_type` |
| Provider Detail Screen | `GET /v1/providers/{id}` |
| Provider Calendar Screen | `GET /v1/providers/{id}/availability` |
| Pricing Screen | `POST /v1/pricing/quote` |
| Booking Confirmation Screen | `POST /v1/bookings`, `PUT /v1/bookings/{id}/confirm` |
| My Bookings Screen | `GET /v1/bookings` |
| Booking Detail Screen | `GET /v1/bookings/{id}`, `GET /v1/bookings/{id}/receipt` |
| Post-Service Rating Screen | `POST /v1/bookings/{id}/feedback` |
| Dispute Screen | `POST /v1/disputes`, `GET /v1/disputes/{id}`, `PUT /v1/disputes/{id}/resolve` |
| Disputes List Screen | `GET /v1/disputes` |
| Provider App — Job Board | `GET /v1/bookings` (filtered by provider) |
| Provider App — Update Status | `PUT /v1/bookings/{id}/status` |
| Debug / Audit Screen | `GET /v1/agent-trace/{runId}/export` |
