# Aura Moda — Backend (Plain PHP)

Framework-free REST API for the Aura Moda e-commerce. PHP 8.1+, PDO/MySQL, native sessions.

## Setup

```bash
cd backend
composer install
cp .env.example .env      # fill in your DB credentials
mysql -u root -p < database/migrations/001_create_tables.sql
mysql -u root -p < database/migrations/002_seed.sql
php -S localhost:8000 -t public
```

In production, point the DocumentRoot to `backend/public` (the included `.htaccess` rewrites everything to `index.php` on Apache).

## Frontend (Angular)

During development, to avoid cross-site cookie issues, it's recommended to use an Angular proxy
(`proxy.conf.json`) that redirects `/api/*` to the backend on `localhost:8000`, so both share an
origin from the browser's perspective. In production, serve Angular and the API under the same
domain (e.g. `/` for the SPA and `/api` for the backend) or configure `ALLOWED_ORIGIN` in `.env`.

## Security implemented

- PDO with prepared statements on every query (no input concatenated into SQL).
- Passwords hashed with `password_hash`/`password_verify`.
- httpOnly + SameSite sessions, `session_regenerate_id` on login/register.
- CSRF: session token validated via the `X-CSRF-Token` header on every POST/PATCH/DELETE (fetch it from `GET /api/csrf-token`).
- Login attempt rate limiting (5 attempts / 15 min per email+IP).
- CORS restricted to `ALLOWED_ORIGIN`.

## Endpoints

| Method | Route                    | Auth | Description |
|--------|--------------------------|------|-------------|
| GET    | /api/csrf-token          | -    | CSRF token to include in headers |
| POST   | /api/auth/register       | -    | Register |
| POST   | /api/auth/login          | -    | Login |
| POST   | /api/auth/logout         | Yes  | Logout |
| GET    | /api/auth/me             | Yes  | Current user |
| GET    | /api/categories          | -    | List categories |
| GET    | /api/products            | -    | List with filters `category`, `size`, `color`, `price_max` |
| GET    | /api/products/{id}       | -    | Detail + variants |
| GET    | /api/cart                | -    | Current cart (session or guest cookie) |
| POST   | /api/cart/items          | -    | Add item (`product_id`, `variant_id`, `quantity`) |
| PATCH  | /api/cart/items/{id}     | -    | Change quantity |
| DELETE | /api/cart/items/{id}     | -    | Remove item |
| POST   | /api/orders              | Yes  | Checkout: creates an order from the cart |
| GET    | /api/orders              | Yes  | User's order history |
| GET    | /api/orders/{id}         | Yes  | Order detail |
| POST   | /api/orders/{id}/pay     | Yes  | Starts payment (currently `StubGateway`) |

## Integrating a real payment method

Implement `Backend\Payments\PaymentGatewayInterface` (e.g. `StripeGateway`, `MercadoPagoGateway`)
and replace the `StubGateway` instance in `OrderController::pay()`. Add the corresponding webhook
endpoint in `public/index.php` to confirm the payment and update the order status via
`Order::updateStatus()`.
