# Laravel Sanctum API Authentication Setup

## Summary

Your Laravel project has been successfully refactored to support **API authentication using Laravel Sanctum** without breaking any existing web functionality.

---

## What Was Added

### 1. **Laravel Sanctum Package**
- Installed `laravel/sanctum` for token-based API authentication
- Created `personal_access_tokens` table for storing API tokens

### 2. **Authentication Migrations**
- **File**: `database/migrations/2024_05_05_000000_create_personal_access_tokens_table.php`
- Creates the table required for Sanctum token storage

### 3. **User Model Enhancement**
- **File**: `app/Models/User.php`
- Added `HasApiTokens` trait to enable token generation
- Users can now create API tokens via `$user->createToken('api-token')`

### 4. **Auth Configuration**
- **File**: `config/auth.php`
- Added `'sanctum'` guard for API authentication
- Web guard remains unchanged for session-based auth

### 5. **API Authentication Controller**
- **File**: `app/Http/Controllers/Api/AuthController.php`
- `POST /api/login` - Authenticate with email/password, returns Bearer token
- `POST /api/logout` - Revoke current token
- `GET /api/me` - Get authenticated user info

### 6. **API Routes**
- **File**: `routes/api.php` (newly created)
- **Public endpoint**: `POST /api/login`
- **Protected endpoints**: All routes use `auth:sanctum` middleware
  - `POST /api/logout`
  - `GET /api/me`
  - `GET /api/admin/metrics`
  - `GET /api/admin/analytics`
  - `GET /api/admin/activity-logs`
  - `GET/POST/PUT/DELETE /api/admin/concerts`
  - `GET/POST/PUT/DELETE /api/admin/users`
  - `GET/POST/PUT/DELETE /api/admin/venues`

### 7. **API Admin Middleware**
- **File**: `app/Http/Middleware/ApiAdminMiddleware.php`
- Checks if user is authenticated AND has admin role
- Returns JSON responses (not redirects) for API requests

### 8. **Bootstrap Configuration**
- **File**: `bootstrap/app.php`
- Registered new API routes explicitly
- Added `api_admin` middleware alias

---

## What Remained Unchanged

✅ **All existing web routes are intact:**
- `/login`, `/register`, `/logout`
- `/admin/*` web dashboard routes
- `/concerts/*` listing and booking pages
- All user and profile management pages

✅ **No modifications to:**
- Web middleware or authentication logic
- Existing controllers (only added new API controllers)
- Database structure or migrations (except Sanctum tokens table)
- Session-based authentication

---

## How to Use the API

### 1. **Login to Get Token**

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@gmail.com",
    "password": "your-password"
  }'
```

**Response:**
```json
{
  "token": "1|EeYFcT2nflk1psOynDdrsYjwDedqM6ijU3nF5cP7fa641b72"
}
```

### 2. **Use Token for Protected Requests**

```bash
curl -X GET http://localhost:8000/api/admin/concerts \
  -H "Authorization: Bearer 1|EeYFcT2nflk1psOynDdrsYjwDedqM6ijU3nF5cP7fa641b72"
```

### 3. **Logout (Revoke Token)**

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer 1|EeYFcT2nflk1psOynDdrsYjwDedqM6ijU3nF5cP7fa641b72"
```

### 4. **Get Current User Info**

```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer 1|EeYFcT2nflk1psOynDdrsYjwDedqM6ijU3nF5cP7fa641b72"
```

---

## Testing in Postman

1. **Login Request**
   - Method: `POST`
   - URL: `http://localhost:8000/api/login`
   - Body (JSON):
     ```json
     {
       "email": "admin@gmail.com",
       "password": "your-password"
     }
     ```
   - Copy the `token` from response

2. **Protected Request (e.g., Get Concerts)**
   - Method: `GET`
   - URL: `http://localhost:8000/api/admin/concerts`
   - Headers tab, add:
     - Key: `Authorization`
     - Value: `Bearer {token}`

---

## Key Features

✅ **Token-based API Authentication** - No sessions required for API  
✅ **Admin-only Endpoints** - API admin routes protected with role check  
✅ **Clean Separation** - Web routes use sessions, API uses tokens  
✅ **Backward Compatible** - Web functionality 100% intact  
✅ **Minimal Setup** - Only essential files created/modified  
✅ **Production Ready** - Uses Laravel's official Sanctum package  

---

## Database Changes

Only one new table was created:
- `personal_access_tokens` - Stores API tokens and their metadata

All existing tables remain unchanged.

---

## Files Modified/Created

### Created:
- `database/migrations/2024_05_05_000000_create_personal_access_tokens_table.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Middleware/ApiAdminMiddleware.php`
- `routes/api.php`

### Modified:
- `app/Models/User.php` (added `HasApiTokens` trait)
- `config/auth.php` (added `sanctum` guard)
- `bootstrap/app.php` (registered API routes and middleware)
- `routes/web.php` (removed old API routes from web context)

---

## Notes

- Tokens are long-lived by default. Configure expiration in `config/sanctum.php` if needed.
- Each token can have different abilities. Currently all tokens have `["*"]` (all abilities).
- Tokens can be revoked individually or all at once per user.
- API routes are automatically prefixed with `/api` (configured in Laravel).

---

## Troubleshooting

**401 Unauthorized on `/api/admin/*` routes?**
- Ensure you're sending `Authorization: Bearer {token}` header
- Make sure the user role is `admin`

**403 Forbidden?**
- The user is authenticated but not an admin
- Check user's `role` field in database

**Token invalid?**
- Token may have been revoked
- Login again to generate a new token

---

