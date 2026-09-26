# Agronomy Plan Deviations

Audit date: 2026-09-19

## Repository state

- The repository is a Laravel 12 application. Git CLI is not available in the current terminal PATH, so Git status/history must be checked from the VS Code source-control UI or a terminal with Git installed.
- `php artisan route:list` currently exposes the legacy web dashboard and `/api/taman/{taman}/telemetry` plus sync and sensor pairing endpoints.
- The latest migration adding `sensor_types`, `sensor_models`, `controller_type`, and `device_connection` is pending. No destructive migration is required.

## Existing contracts to preserve

- `tamans.type` is already an enum with `corn`, `greenhouse`, `rice`, and `custom`.
- Existing sensor configuration is stored across `sensor_types`, `sensor_models`, `controller_type`, and `device_connection` JSON/string columns.
- Existing telemetry stores nullable `ph`, `moisture`, `temperature`, `ec`, `health_score`, `health_status`, and `recorded_at`.
- Existing client integrations use `/api/taman/{taman}/telemetry` and `/api/taman/{taman}/sync`; these routes remain compatible while the sensor-aware payload is added.
- Ownership is enforced locally with controller checks rather than a `TamanPolicy`.

## Differences from the supplied snapshot assumptions

- Telemetry and farm activity persistence already exist, but simulation is currently random/drift-based in `TelemetryController` and `public/js/script.js`; it is not deterministic or sensor-aware.
- The dashboard view is currently a four-metric, hardware-pairing-oriented page with many inline Indonesian strings and legacy theme classes. It does not yet render a server-embedded sensor-aware payload.
- The current API returns raw telemetry rows and does not expose lifecycle, availability, decision domains, coverage, confidence, or reason/action codes.
- No `app/Services/Agronomy` engine, centralized catalog, agronomy rules config, localization API, policy, or agronomy test suite exists yet.
- The plan's proposed `soil_type`, `indicator_mode`, and versioned `sensor_config` fields are not present in the current schema. They will be introduced additively and existing columns will be adapted rather than renamed.
- Browser USB detection is intentionally treated as a candidate only. A generic USB connection cannot reliably identify ESP32, ESP8266, or Arduino without Web Serial/device probing or a hardware handshake. Pairing therefore requires the explicitly selected board type and the API rejects board mismatches; no USB presence alone can make a farm live.

## Adaptation order

1. Add centralized catalog/rules configuration and a pure PHP agronomy engine.
2. Add additive `soil_type`, `indicator_mode`, and versioned `sensor_config` support while preserving legacy sensor fields.
3. Replace random client telemetry with server-owned, deterministic telemetry contracts and sensor availability semantics.
4. Add the shared translation contract, then refactor dashboard rendering and theme tokens.
5. Expand wizard, lifecycle UI, and focused tests incrementally.

The implementation must not use `migrate:fresh`, `migrate:reset`, or `db:wipe`.
