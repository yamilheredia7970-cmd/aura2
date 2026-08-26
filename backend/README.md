# Aura Moda — Backend (PHP puro)

API REST sin framework para el e-commerce Aura Moda. PHP 8.1+, PDO/MySQL, sesiones nativas.

## Setup

```bash
cd backend
composer install
cp .env.example .env      # completar credenciales de DB
mysql -u root -p < database/migrations/001_create_tables.sql
mysql -u root -p < database/migrations/002_seed.sql
php -S localhost:8000 -t public
```

En producción, apuntar el DocumentRoot a `backend/public` (el `.htaccess` incluido reescribe todo a `index.php` en Apache).

## Frontend (Angular)

Durante desarrollo, para evitar problemas de cookies cross-site, se recomienda usar un proxy en Angular
(`proxy.conf.json`) que redirija `/api/*` al backend en `localhost:8000`, para que ambos compartan origen
desde la perspectiva del navegador. En producción, servir Angular y la API bajo el mismo dominio
(p. ej. `/` para el SPA y `/api` para el backend) o configurar `ALLOWED_ORIGIN` en `.env`.

## Seguridad implementada

- PDO con prepared statements en toda consulta (sin concatenar input en SQL).
- Contraseñas con `password_hash`/`password_verify`.
- Sesiones httpOnly + SameSite, `session_regenerate_id` en login/registro.
- CSRF: token de sesión validado por header `X-CSRF-Token` en todo POST/PATCH/DELETE (obtenerlo desde `GET /api/csrf-token`).
- Rate limiting de intentos de login (5 intentos / 15 min por email+IP).
- CORS restringido a `ALLOWED_ORIGIN`.

## Endpoints

| Método | Ruta                     | Auth | Descripción |
|--------|--------------------------|------|-------------|
| GET    | /api/csrf-token          | -    | Token CSRF para incluir en headers |
| POST   | /api/auth/register       | -    | Registro |
| POST   | /api/auth/login          | -    | Login |
| POST   | /api/auth/logout         | Sí   | Logout |
| GET    | /api/auth/me             | Sí   | Usuario actual |
| GET    | /api/categories          | -    | Listado de categorías |
| GET    | /api/products            | -    | Listado con filtros `category`, `size`, `color`, `price_max` |
| GET    | /api/products/{id}       | -    | Detalle + variantes |
| GET    | /api/cart                | -    | Carrito actual (sesión o guest cookie) |
| POST   | /api/cart/items          | -    | Agregar ítem (`product_id`, `variant_id`, `quantity`) |
| PATCH  | /api/cart/items/{id}     | -    | Cambiar cantidad |
| DELETE | /api/cart/items/{id}     | -    | Quitar ítem |
| POST   | /api/orders              | Sí   | Checkout: crea orden desde el carrito |
| GET    | /api/orders              | Sí   | Historial del usuario |
| GET    | /api/orders/{id}         | Sí   | Detalle de una orden |
| POST   | /api/orders/{id}/pay     | Sí   | Inicia pago (actualmente `StubGateway`) |

## Integrar un medio de pago real

Implementar `Backend\Payments\PaymentGatewayInterface` (p. ej. `StripeGateway`, `MercadoPagoGateway`)
y reemplazar la instancia de `StubGateway` en `OrderController::pay()`. Agregar el endpoint de webhook
correspondiente en `public/index.php` para confirmar el pago y actualizar el estado de la orden vía
`Order::updateStatus()`.
