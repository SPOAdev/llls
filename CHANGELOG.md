# Changelog

## [1.0.0] - 2025-04-16

### Added
- Laravel 11 base installation with modular structure.
- License model and migration with validation rules and status.
- Product model with external provider support (`local`, `hotmart`, etc.).
- License verification endpoint with domain and expiration checks.
- Optional `update_payload` support for passing update info to clients.
- Webhook endpoint to receive external platform events.
- Hotmart integration: support for `PURCHASE_COMPLETE`, `PURCHASE_CANCELED`, `SWITCH_PLAN`, and more.
- Dynamic logging system (`llls_log`) configurable via `llls.debug`.
- Artisan commands:
  - `licenses:check-expirations`
  - `licenses:update-payload`
  - `licenses:clear-payload`
  - `licenses:show-payload`
  - `licenses:renew`

### Changed
- Validation rules now support `cycle` with `lifetime`, `monthly`, etc.
- Webhook `update_license` action now updates expiration if `date_next_charge` is sent.

### Fixed
- Hotmart integration handles fallback fields (`ucode`, `id`, `subscriber`, `buyer`) robustly.
- Webhook service now respects `external_id` correctly.

### Docs
- Full usage instructions in `README.md`.
- Composer connector: https://github.com/spoadev/llls-connector

---

## [Unreleased]

- Stripe integration (planned).
- Admin panel for managing licenses.
- More granular permission control
- License history / audit
- Rate limiting & abuse protection