# Light Laravel License Server (LLLS)

This is the official **Light License Laravel Server** (LLLS) built on top of Laravel 11. 
It provides a lightweight and extensible API for license verification and client update integration. Check the official [LLLS Connector](https://github.com/spoadev/llls-connector) to integrate it to your plugins, addons or any development you wish to consult your client licenses.

---

## Requirements

- PHP 8.2 or higher
- Laravel 11
- MySQL / PostgreSQL / SQLite supported
- Composer

---

## Setup

1. Clone the repository:

```bash
git clone git@github.com:spoadev/llls.git
cd llls
```

2. Install dependencies:

```bash
composer install
```

3. Set up the environment:

```bash
cp .env.example .env
php artisan key:generate
```

4. Set up the database:

```bash
php artisan migrate
php artisan db:seed
```

This will create the basic roles (`admin`, `user`) and assign default permissions:

- **admin**: `manage_license`, `manage_user`, `query_license`
- **user**: `query_license`

If user with ID 1 exists, it will automatically be assigned the `admin` role.

---

## Note about `/bootstrap` folder

The `/bootstrap/app.php` and `/bootstrap/providers.php` files ***will be replaced*** if you clone this repo. 

---

## License Configuration

The core settings are defined in `config/llls.php`:

```php
return [
    'debug' => env('LLLS_DEBUG', false),

    // Optional model relationship to link licenses to products
    'product_model' => null,

    // License duration cycles (used when assigning expirations dynamically)
    'cycles' => [
        'daily' => fn () => now()->addDay(),
        'weekly' => fn () => now()->addWeek(),
        'monthly' => fn () => now()->addMonth(),
        'quarterly' => fn () => now()->addMonths(3),
        'semiannually' => fn () => now()->addMonths(6),
        'annually' => fn () => now()->addYear(),
    ],

    // Defines how often the license cleanup runs (via Laravel scheduler)
    'check_license_schedule' => 'hourly',
];
```

---

## Creating a License (Tinker)

You can create test licenses directly via Tinker:

```bash
php artisan tinker
```

```php
License::create([
  'user_id' => 1,
  'license_key' => 'TEST-ABC-123',
  'expires_at' => now()->addMonths(3),
  'validation_rules' => [
    'domain_mode' => 'disabled'
  ],
  'status' => 'active'
]);
```

If `expires_at` is set to `null`, the license is considered lifetime.

`validation_rules` Examples (supported options)
- **No domain validation (default)**
```php
'validation_rules' => [
    'domain_mode' => 'disabled'
]
```
→ License will work from any domain.
→ No domain checking at all.

- **Single domain lock (auto-binding)**
```php
'validation_rules' => [
    'domain_mode' => 'single'
]
```
→ First successful verification will store domain in the license.
→ Subsequent requests must always use that domain.

- **Multi-domain allowed (pre-defined)**
```php
'validation_rules' => [
    'domain_mode' => 'multi',
    'domains' => [
        'domain1.com',
        'domain2.com',
        'another.com'
    ]
]
```
→ License will only be valid if the requested domain matches one of the listed domains.

---

## API Endpoint: License Verification

`POST /api/verify`

### Parameters:
- `license_key` (required)
- `domain` (optional depending on validation_rules)

### Example Response:

```json
{
  "status": "valid",
  "message": "License is valid",
  "expires_at": "2025-07-15 00:00:00",
  "update": {
    "latest_version": "2.3.5",
    "download_url": "https://yourdomain.com/update.zip"
  }
}
```

---

## Managing `update_payload`

These artisan commands allow you to fully manage the `update_payload` field of any license.


### Create or Update `update_payload`

```bash
php artisan license:update-payload {license_key} --data=key=value --data=key=value ...
```

Creates or updates the `update_payload` content for a given license.

#### Example:

```bash
php artisan license:update-payload TEST-ABC-123
    --data=latest_version=2.3.5
    --data=download_url=https://domain.com/update.zip
    --data=changelog="Bug fixes and improvements"
    --data=force_update=true
```

### Show `update_payload`

```bash
php artisan license:show-payload {license_key}
```

Displays the current `update_payload` stored in a license.

#### Example:

```bash
php artisan license:show-payload TEST-ABC-123
```

#### Output:

```json
{
  "latest_version": "2.3.5",
  "download_url": "https://domain.com/update.zip",
  "changelog": "Bug fixes and improvements",
  "force_update": "true"
}
```

### Clear `update_payload`

```bash
php artisan license:clear-payload {license_key}
```

Removes the entire `update_payload` from a license.

#### Example:

```bash
php artisan license:clear-payload TEST-ABC-123
```

## Commands Summary

| Command                          | Purpose                                    |
|---------------------------------|--------------------------------------------|
| license:update-payload          | Create or update `update_payload`         |
| license:show-payload            | View current `update_payload`             |
| license:clear-payload           | Remove `update_payload` completely        |

---

## Scheduled License Expiration

Licenses that are `active` and have an `expires_at` date in the past will be automatically set to `inactive`.

This is handled by the scheduled command:

```php
php artisan licenses:check-expirations
```

Laravel runs this using the scheduler, defined in `ScheduledTaskServiceProvider.php`:

```php
$schedule->command('licenses:check-expirations')->hourly();
```

Be sure to set up your server cron:

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## License Status Values

- `active`: Valid and functioning license
- `inactive`: Expired or deactivated
- `cancelled`: Permanently cancelled license

---

## Debug Logging

If `debug` is enabled in `config/llls.php`, every license verification attempt will be logged to `storage/logs/laravel.log` with:
- license key
- IP
- domain
- status

---

## LLLS Connector

The official [LLLS Connector](https://github.com/spoadev/llls-connector) PHP package for this server is available.
This package allows any PHP application (Laravel, WordPress, Symfony, custom) to:
- Verify license keys
- Send domain information
- Automatically receive update payloads

---

## ToDo

- Web-based CRUD for licenses and products
- Role-based management UI
- Dynamic expiration based on `cycles` with UI
- License usage analytics
- Client-side SDKs for more platforms (JS, Python, etc.)
- Payload templating and inheritance

---

# Changelog
## [1.0.0] - 2025-04-15
- Initial Laravel 11 setup
- User, roles and permissions system using Spatie
- `/api/verify` endpoint with:
  - License status validation
  - Domain validation (disabled, single, multi)
  - Expiration handling
  - Debug logging
- License expiration cleanup via scheduler (`licenses:check-expirations`)
- Config file `config/llls.php` with:
  - License cycle definitions
  - Debug toggle
  - Scheduler frequency
- License update payload support (`update_payload`)
- Artisan commands to manage payloads:
  - `license:update-payload`
  - `license:show-payload`
  - `license:clear-payload`

### License
MIT
