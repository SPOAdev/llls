# Light Laravel License Server (LLLS)

This is the official **Light License Laravel Server** (LLLS) built on top of Laravel 11. 
It provides a lightweight and extensible API for license verification and client update integration. Supports domain validation, Webhook integration (Hotmart), license payload delivery and expiration cycles. Check the official [LLLS Connector](https://github.com/spoadev/llls-connector) to integrate it to your plugins, addons or any development you wish to consult your client licenses.

**Basic Flowchart**
-- Create a license (model `License` provided) linked to a `User` by `user_id` (required)
-- Renew the license using `php artisan licenses:renew {license_key}` in your custom logic or scheduled tasks
-- Send a custom `update_payload` to your licensed software when it queries the license status (for update purposes)

**Advanced Things**
-- Optionally create a licenciable product (model `Product` provided)
-- Products with a provider different from `local` must have a corresponding `Service` (`app/Services/`)
-- That service must be registered in the `webhooks` map (`config/llls.php`)

---

## 🚀 Requirements

- PHP >= 8.2
- Laravel >= 11
- MySQL / MariaDB

---

## ⚙️ Installation

```bash
git clone git@github.com:spoadev/llls.git
cd llls
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

## 🔐 Roles & Permissions

Seeder included:

| Role  | Permissions                   |
|-------|-------------------------------|
| admin | manage_license, manage_user, query_license |
| user  | query_license                 |

User ID 1 (if exists) gets assigned the `admin` role.

---

## 🔧 Config

### `config/llls.php`

```php
return [
  	// Debug directive from .env file
    'debug' => env('LLLS_DEBUG', false),
  	
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
  	'check_license_schedule' => 'daily',
  
  	// Webhooks
  	'webhooks' => [
        'hotmart' => [
            'events' => [
                'PURCHASE_APPROVED' => 'log_only',
                'PURCHASE_COMPLETE' => 'create_license',
                'PURCHASE_REFUNDED' => 'cancel_license',
                'PURCHASE_CANCELED' => 'cancel_license',
                'PURCHASE_CHARGEBACK' => 'cancel_license',
                'SUBSCRIPTION_CANCELLATION' => 'cancel_license',
                'SWITCH_PLAN' => 'update_license',
                'UPDATE_SUBSCRIPTION_CHARGE_DATE' => 'update_license',
            ],
        ],
    ],
];
```

---

## 🧪 Creating a License (Tinker)

You can manually create a license for testing purposes using Tinker.

**Step-by-step for a local product (optional) and license:**

```php
// Create a local product
$product = \App\Models\Product::create([
    'name' => 'Demo Product',
    //'provider' => 'local'
]);

// Create a user
$user = \App\Models\User::create([
    'name' => 'Demo User',
    'email' => 'demo@example.com',
    'password' => bcrypt('secret'),
]);

// Create the license
$license = \App\Models\License::create([
    'user_id' => $user->id,
    'product_id' => $product->id, //optional
    'license_key' => strtoupper(Str::uuid()),
    'status' => 'active',
    'validation_rules' => [
        'domain_mode' => 'disabled',
        'cycle' => 'monthly',
    ],
    'expires_at' => now()->addMonth(),
]);
```

> 🧠 Tip: If `expires_at` is `null`, the license is considered **lifetime**.

---

## 🧪 License Verification

POST `/api/verify`  
```json
{
  "license_key": "YOUR-LICENSE-KEY",
  "domain": "example.com"
}
```

### Possible validation rules:
```json
{
  "domain_mode": "single | multi | disabled",
  "domain": "example.com",
  "domains": ["example.com", "sub.example.com"],
  "cycle": "monthly"
}
```
---

## 🔁 Scheduled License Expiration

LLLS includes a scheduled task to automatically deactivate expired licenses.

**🛠 Artisan Command:**

```bash
php artisan licenses:check-expirations
```

This command reviews all licenses with status `active` and:

- If the license has a past `expires_at`, it is marked as `inactive`.
- The logic respects the product's `provider`:
  - If the product is `local` or `product_id` is null, it uses `validation_rules` to determine expiration (key `cycle`).
  - If the product is from an external provider (`hotmart`, `stripe`, etc.), it only compares `expires_at` to the current date.

**🗓️ Schedule Configuration:**
This command is scheduled to run hourly by default using Laravel's scheduler. You can change this in `config/llls.php`:

```php
'check_license_schedule' => 'hourly', // Options: hourly, daily, weekly...
```

Make sure to register Laravel's scheduler in your server's crontab:

```bash
* * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1
```

---

## 🔄 Managing `update_payload`

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

---

## 🛠 Artisan Commands

| Command | Description |
|--------|-------------|
| `php artisan licenses:show-payload {license_key}` | View `update_payload` of a license |
| `php artisan licenses:update-payload {license_key} '{"url": "https://..."}'` | Update `update_payload` |
| `php artisan licenses:clear-payload {license_key}` | Clear `update_payload` |
| `php artisan licenses:check-expirations` | Disable expired licenses |
| `php artisan licenses:renew {license_key}` | Manually renew license cycle |

---

## 🌐 Webhook Endpoint

POST `/api/webhooks/hotmart`  
Receives any Hotmart v2 webhook payload. All events are handled based on the map in `config/llls.php`.

---

## 🔗 LicenseConnector PHP Library

We recommend using the official connector:
**GitHub**: [https://github.com/spoadev/llls-connector](https://github.com/spoadev/llls-connector)  
Install via Composer:

```bash
composer require spoadev/llls-connector
```

---

## 📂 Deployment Notes

- The Laravel `public/` directory must be set as the root in your webserver (vhost).
- Logs are stored using default Laravel `storage/logs/laravel.log`.
- No frontend views included – fully headless.

---

## 📌 To Do

- UI admin panel
- Stripe webhook integration
- More granular permission control
- License history / audit
- Rate limiting & abuse protection

---

## 📝 Changelog

### v1.0.0 (2025-04-16)
- Initial Laravel 11 setup
- Roles & Permissions with Spatie
- License validation API
- Webhook support for Hotmart
- Artisan commands for payloads & expirations
- Lifetime & cycle-based licenses
- Metadata for products & licenses

## License

MIT