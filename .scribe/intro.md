# Introduction

AI-powered service orchestration API for Karachi, Pakistan. 6-agent pipeline: intent parsing → provider matching → dynamic pricing → booking simulation → follow-up → dispute resolution.

<aside>
    <strong>Base URL</strong>: <code>http://localhost</code>
</aside>

This API powers the ServiceHub mobile app. All /api/v1/* endpoints require a Bearer token from POST /api/auth/login. Quick start: register → login → POST /api/v1/service-requests → GET /api/v1/providers → POST /api/v1/pricing/quote → POST /api/v1/bookings. All bookings are simulated.

