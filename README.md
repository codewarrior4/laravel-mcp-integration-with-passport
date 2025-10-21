# Laravel MCP with Passport OAuth Setup Guide

This guide documents the complete setup process for implementing OAuth authentication in a Laravel MCP (Model Context Protocol) server using Laravel Passport.

## ✅ Working Setup

This project has been fully configured and tested with:
- OAuth 2.0 authentication with PKCE support
- UUID primary keys for users
- Session-based web authentication
- Protected and public MCP endpoints
- MCP Inspector integration

## Quick Start

If this is already set up, just run:

```bash
# Start the server
php artisan serve

# In another terminal, test with MCP Inspector
php artisan mcp:inspector mcp/admin
```

**OAuth Credentials:**
- Check your database for the client ID and secret in `oauth_clients` table
- Test user: `test@example.com` / `password`

## Prerequisites

- Laravel 11.x
- PHP 8.1+
- MySQL 8.0+
- Composer

## Step 1: Install Laravel Passport

```bash
php artisan install:api --passport
```

This command will:
- Install Laravel Passport package
- Publish and run Passport migrations
- Generate encryption keys for secure access tokens

## Step 2: Configure User Model for UUIDs

Update `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens, Notifiable, HasUuids;
    
    // ... rest of your model
}
```

**Important**: The `HasUuids` trait is required if your users table uses UUID primary keys.

## Step 3: Configure Authentication Guard

Update `config/auth.php`:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'passport',  // Changed from 'token' to 'passport'
        'provider' => 'users',
    ],
],
```

## Step 4: Fix Passport Migrations for UUIDs

**Critical**: If using UUIDs, update Passport migrations to use `foreignUuid` instead of `foreignId`:

Edit these migration files:
- `database/migrations/*_create_oauth_auth_codes_table.php`
- `database/migrations/*_create_oauth_access_tokens_table.php`
- `database/migrations/*_create_oauth_device_codes_table.php`

Change:
```php
$table->foreignId('user_id')->index();
```

To:
```php
$table->foreignUuid('user_id')->index();
```

Also update `database/migrations/*_create_users_table.php` sessions table:
```php
$table->foreignUuid('user_id')->nullable()->index();
```

Then run migrations:
```bash
php artisan migrate:fresh
```

## Step 5: Configure CORS

CORS (Cross-Origin Resource Sharing) is critical for OAuth flows, especially when the MCP inspector or AI agents run on different origins.

Update `config/cors.php`:

```php
<?php

return [
    'paths' => ['api/*', 'mcp/*', 'oauth/*', '.well-known/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],  // For production, specify exact origins

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,  // Required for OAuth with cookies
];
```

### CORS Configuration Explained

- **paths**: Include `oauth/*` for OAuth endpoints and `.well-known/*` for OAuth discovery
- **allowed_origins**: Set to `['*']` for development. In production, specify exact origins like `['https://your-domain.com']`
- **supports_credentials**: Must be `true` to allow cookies and authentication headers in cross-origin requests
- **allowed_methods**: `['*']` allows all HTTP methods (GET, POST, OPTIONS, etc.)
- **allowed_headers**: `['*']` allows all headers including `Authorization` and custom headers

### Production CORS Configuration

For production, tighten CORS settings:

```php
'allowed_origins' => [
    'https://your-production-domain.com',
    'https://mcp-inspector.example.com',
],

'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

'supports_credentials' => true,
```

## Step 5: Configure Passport Authorization View

Update `app/Providers/AppServiceProvider.php`:

```php
use Laravel\Passport\Passport;

public function boot(): void
{
    Passport::authorizationView('mcp.authorize');
}
```

## Step 6: Publish MCP Authorization View

```bash
php artisan vendor:publish --tag=mcp-views
```

This creates `resources/views/mcp/authorize.blade.php`.

## Step 7: Fix Authorization View CSS Loading

Edit `resources/views/mcp/authorize.blade.php` and replace the `@vite` directive with inline styles to avoid loading issues:

```php
<style>
    body { font-family: sans-serif; margin: 0; padding: 0; }
    .bg-background { background: #f5f5f5; }
    .text-foreground { color: #333; }
    .bg-card { background: white; }
    .text-card-foreground { color: #333; }
    .border { border: 1px solid #e5e7eb; }
    .rounded-lg { border-radius: 0.5rem; }
    .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
    .text-primary { color: #4f46e5; }
    .bg-primary { background: #4f46e5; }
    .text-primary-foreground { color: white; }
    .bg-muted\/50 { background: rgba(243, 244, 246, 0.5); }
    .text-muted-foreground { color: #6b7280; }
    button:hover { opacity: 0.9; }
</style>
```

## Step 8: Fix Authorization View State Parameter

Edit `resources/views/mcp/authorize.blade.php` and update the hidden state inputs:

Change:
```html
<input type="hidden" name="state" value="">
```

To:
```html
<input type="hidden" name="state" value="{{ $request->state ?? '' }}">
```

This is required for both the approve and deny forms.

## Step 9: Configure MCP OAuth Routes

Update `routes/ai.php`:

```php
<?php

use App\Mcp\Servers\AdminServer;
use App\Mcp\Servers\WarriorServer;
use Laravel\Mcp\Facades\Mcp;

// OAuth discovery and client registration routes
Mcp::oauthRoutes();

// Public MCP server (no auth)
Mcp::web('/mcp/warrior', WarriorServer::class);

// Admin MCP server (OAuth protected)
Mcp::web('/mcp/admin', AdminServer::class)
    ->middleware('auth:api');
```

## Step 10: Setup Web Authentication

You need a web authentication system for users to login before authorizing OAuth clients.

### Option A: Simple Login (for testing)

Create `routes/auth.php`:

```php
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
```

Create `app/Http/Controllers/Auth/AuthenticatedSessionController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
```

Create `app/Http/Requests/Auth/LoginRequest.php`:

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
```

Create `resources/views/auth/login.blade.php`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Login - {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif;background:#f5f5f5;margin:0;">
    <div style="background:white;padding:40px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);max-width:400px;width:100%;">
        <h2 style="margin:0 0 10px;text-align:center;">MCP OAuth Login</h2>
        <p style="color:#666;text-align:center;margin:0 0 30px;">Login to authorize the application</p>
        
        @if($errors->any())
            <div style="background:#fee;color:#c33;padding:10px;border-radius:4px;margin-bottom:20px;">
                {{ $errors->first() }}
            </div>
        @endif
        
        <form method="POST" action="/login">
            @csrf
            
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:5px;font-weight:500;">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-size:14px;">
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:5px;font-weight:500;">Password</label>
                <input type="password" name="password" required
                    style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-size:14px;">
            </div>
            
            <button type="submit" style="width:100%;padding:12px;font-size:16px;cursor:pointer;background:#4f46e5;color:white;border:none;border-radius:6px;font-weight:500;">
                Login
            </button>
        </form>
    </div>
</body>
</html>
```

### Option B: Use Laravel Breeze (recommended for production)

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
php artisan migrate
npm install && npm run build
```

## Step 10: Update Web Routes

Update `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return 'Dashboard - You are logged in as ' . auth()->user()->email;
    })->name('dashboard');
});

require __DIR__.'/auth.php';
```

**Note**: No redirect from `/authorize` to `/oauth/authorize` is needed. The `Mcp::oauthRoutes('oauth')` call automatically registers Passport routes at `/oauth/authorize`.

## Step 11: Create OAuth Client

```bash
php artisan passport:client
```

When prompted:
- Enter client name (e.g., "MCP Admin Inspector")
- Leave redirect URI empty (press Enter)

Save the generated Client ID and Client Secret.

## Step 12: Update Client Redirect URI

The redirect URI needs to be stored as a JSON array. Update it manually:

```bash
mysql -u root your_database_name
```

```sql
UPDATE oauth_clients 
SET redirect_uris = '["http://localhost:6274/oauth/callback"]' 
WHERE id = 'your-client-id';
```

Or use tinker:

```bash
php artisan tinker
```

```php
$client = \Laravel\Passport\Client::find('your-client-id');
$client->redirect = ['http://localhost:6274/oauth/callback'];
$client->save();
```

## Step 13: Create Test User

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password')
]);
```

## Step 14: Test OAuth Flow

1. Start MCP inspector:
```bash
php artisan mcp:inspector mcp/admin
```

2. In the inspector dashboard:
   - Enter your Client ID
   - Enter your Client Secret
   - Click "Connect"

3. Browser will open to authorization page:
   - Login with your test user credentials
   - Click "Authorize"
   - Browser redirects back to inspector
   - Inspector now has access to protected MCP server

## OAuth Flow Diagram

```
┌─────────────┐                                    ┌──────────────┐
│             │  1. Request /mcp/admin             │              │
│ MCP         │───────────────────────────────────>│   Laravel    │
│ Inspector   │                                    │   MCP App    │
│             │  2. 401 Unauthenticated            │              │
│             │<───────────────────────────────────│              │
└─────────────┘                                    └──────────────┘
       │                                                   │
       │ 3. Redirect to /oauth/authorize                  │
       │──────────────────────────────────────────────────>
       │                                                   │
       │ 4. Not logged in → redirect to /login            │
       │<──────────────────────────────────────────────────
       │                                                   │
       │ 5. User enters credentials                       │
       │──────────────────────────────────────────────────>
       │                                                   │
       │ 6. Login successful → redirect to /oauth/authorize
       │<──────────────────────────────────────────────────
       │                                                   │
       │ 7. Show authorization screen                     │
       │<──────────────────────────────────────────────────
       │                                                   │
       │ 8. User clicks "Authorize"                       │
       │──────────────────────────────────────────────────>
       │                                                   │
       │ 9. Redirect with authorization code              │
       │<──────────────────────────────────────────────────
       │                                                   │
       │ 10. Exchange code for access token               │
       │──────────────────────────────────────────────────>
       │                                                   │
       │ 11. Return access token                          │
       │<──────────────────────────────────────────────────
       │                                                   │
       │ 12. Access /mcp/admin with token                 │
       │──────────────────────────────────────────────────>
       │                                                   │
       │ 13. Return MCP server response                   │
       │<──────────────────────────────────────────────────
```

## Troubleshooting

### Issue: "invalid_client" error

**Solution**: Check that:
- Client ID exists in `oauth_clients` table
- `redirect_uris` is stored as JSON array: `["http://localhost:6274/oauth/callback"]`
- Client is not revoked
- You're using the **plain text** client secret (not the hashed version from database)

### Issue: "invalid_request" - Check the `client_secret` parameter

**Solution**:
- Make sure you're using the original client secret shown when you created the client
- The database stores a hashed version - you need the original plain text secret
- If lost, create a new client: `php artisan passport:client --no-interaction`

### Issue: Login loop - redirects back to login after successful authentication

**Solution**: 
- **Most common**: Sessions table `user_id` column type mismatch
  - If using UUIDs: Change `foreignId('user_id')` to `foreignUuid('user_id')` in sessions table migration
  - Run `php artisan migrate:fresh`
- Clear sessions: `mysql -u root your_db -e "TRUNCATE TABLE sessions;"`
- Clear caches: `php artisan optimize:clear`
- Ensure `SESSION_DRIVER=database` in `.env`

### Issue: "The request is missing a required parameter" during OAuth authorization

**Solution**:
- Check that the `state` parameter is being passed in the authorization form
- Update `resources/views/mcp/authorize.blade.php`:
  ```html
  <input type="hidden" name="state" value="{{ $request->state ?? '' }}">
  ```

### Issue: UUID compatibility errors / "Invalid user_id"

**Solution**:
- **Critical**: All foreign keys referencing `users.id` must use `foreignUuid()` if users table uses UUIDs
- Update these migrations:
  - `*_create_oauth_auth_codes_table.php`
  - `*_create_oauth_access_tokens_table.php`
  - `*_create_oauth_device_codes_table.php`
  - `*_create_users_table.php` (sessions table)
  - `*_create_transactions_table.php` (if exists)
- Add `HasUuids` trait to User model
- Run `php artisan migrate:fresh`

### Issue: "Failed to fetch" error on authorization

**Solution**:
- Remove `@vite` directive from authorize view
- Use inline CSS instead
- Disable JavaScript in authorize view if needed

### Issue: CORS errors in browser console

**Solution**:
- Verify `config/cors.php` includes `oauth/*` in paths
- Ensure `supports_credentials` is set to `true`
- Check that `allowed_origins` includes the requesting origin
- Clear config cache: `php artisan config:clear`

### Issue: Preflight OPTIONS request fails

**Solution**:
- Ensure CORS middleware is registered in `bootstrap/app.php`
- Verify `allowed_methods` includes `OPTIONS`
- Check that web server (nginx/Apache) doesn't block OPTIONS requests

## Security Considerations

1. **Never commit OAuth keys**: Add to `.gitignore`:
   ```
   storage/oauth-*.key
   ```

2. **Use HTTPS in production**: Update `.env`:
   ```
   APP_URL=https://your-domain.com
   ```

3. **Restrict CORS origins in production**: Update `config/cors.php`:
   ```php
   'allowed_origins' => ['https://your-domain.com'],
   ```

4. **Rotate secrets regularly**: Generate new OAuth clients periodically

5. **Implement scopes**: Define specific permissions for different access levels

6. **Monitor OAuth usage**: Log authorization attempts and token usage

7. **Rate limit OAuth endpoints**: Protect against brute force attacks

## Additional Resources

- [Laravel Passport Documentation](https://laravel.com/docs/passport)
- [Laravel MCP Documentation](https://github.com/laravel/mcp)
- [OAuth 2.0 Specification](https://oauth.net/2/)
- [CORS Documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)

## Summary

You now have a fully functional Laravel MCP server with OAuth authentication and CORS configured:
- ✅ Public MCP server at `/mcp/warrior` (no auth required)
- ✅ Protected MCP server at `/mcp/admin` (OAuth required)
- ✅ OAuth authorization flow with user login
- ✅ CORS configured for cross-origin requests
- ✅ Secure token-based API access

AI agents can now authenticate and access your protected MCP server using standard OAuth 2.0 flows from any origin.
