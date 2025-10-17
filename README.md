# Laravel MCP with Passport OAuth Setup Guide

This guide documents the complete setup process for implementing OAuth authentication in a Laravel MCP (Model Context Protocol) server using Laravel Passport.

## Prerequisites

- Laravel 11.x
- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js & NPM

## Step 1: Install Laravel Passport

```bash
php artisan install:api --passport
```

This command will:
- Install Laravel Passport package
- Publish and run Passport migrations
- Generate encryption keys for secure access tokens

## Step 2: Configure User Model

Update `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens, Notifiable;
    
    // ... rest of your model
}
```

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

## Step 4: Configure CORS

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

## Step 8: Configure MCP OAuth Routes

Update `routes/ai.php`:

```php
<?php

use App\Mcp\Servers\AdminServer;
use App\Mcp\Servers\WarriorServer;
use Laravel\Mcp\Facades\Mcp;

// OAuth discovery and client registration routes
Mcp::oauthRoutes('oauth');

// Public MCP server (no auth)
Mcp::web('/mcp/warrior', WarriorServer::class);

// Admin MCP server (OAuth protected)
Mcp::web('/mcp/admin', AdminServer::class)
    ->middleware('auth:api');
```

## Step 9: Setup Web Authentication

You need a web authentication system for users to login before authorizing OAuth clients.

### Option A: Simple Login (for testing)

Create `routes/auth.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('login');
    })->name('login');
    
    Route::post('login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (auth()->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }
        
        return back()->withErrors(['email' => 'Invalid credentials']);
    });
});

Route::middleware('auth')->group(function () {
    Route::post('logout', function (\Illuminate\Http\Request $request) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
```

Create `resources/views/login.blade.php`:

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

### Issue: Login doesn't redirect back to authorization

**Solution**: 
- Clear sessions: `TRUNCATE TABLE sessions;`
- Clear caches: `php artisan optimize:clear`
- Ensure `SESSION_DRIVER=database` in `.env`

### Issue: "Failed to fetch" error on authorization

**Solution**:
- Remove `@vite` directive from authorize view
- Use inline CSS instead
- Disable JavaScript in authorize view if needed

### Issue: UUID compatibility errors

**Solution**:
- If using UUIDs, ensure all Passport migrations use `uuid()` or `string(36)` for `user_id` columns
- Or remove `HasUuids` trait from User model if using bigint IDs

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
