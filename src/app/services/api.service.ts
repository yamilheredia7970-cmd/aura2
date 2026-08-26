import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of, throwError } from 'rxjs';
import { catchError, map, switchMap, tap } from 'rxjs/operators';
import { AuthUser, CartItem, Category, Product, ProductVariant } from '../models';

interface RawProduct {
  id: number;
  name: string;
  short_description: string | null;
  price: string;
  old_price: string | null;
  rating: string;
  review_count: number;
  image_url: string | null;
  category_name: string | null;
  is_new: number;
  is_bestseller: number;
  variants: ProductVariant[];
}

interface RawCartItem {
  id: number;
  quantity: number;
  product_id: number;
  variant_id: number;
  name: string;
  price: string;
  image_url: string | null;
  size: string;
  color: string;
  stock: number;
}

export interface ProductFilters {
  category?: string;
  size?: string;
  color?: string;
  price_max?: number;
}

@Injectable({ providedIn: 'root' })
export class ApiService {
  private readonly http = inject(HttpClient);
  private readonly base = '/api';
  private csrfToken: string | null = null;

  // ---- Catalog ----

  getCategories(): Observable<Category[]> {
    return this.http
      .get<{ categories: { id: number; name: string; image_url: string }[] }>(
        `${this.base}/categories`,
        { withCredentials: true },
      )
      .pipe(
        map((res) =>
          res.categories.map((c) => ({ id: c.id, name: c.name, imageUrl: c.image_url })),
        ),
      );
  }

  getProducts(filters: ProductFilters = {}): Observable<Product[]> {
    const params: Record<string, string> = {};
    if (filters.category) params['category'] = filters.category;
    if (filters.size) params['size'] = filters.size;
    if (filters.color) params['color'] = filters.color;
    if (filters.price_max !== undefined) params['price_max'] = String(filters.price_max);

    return this.http
      .get<{ products: RawProduct[] }>(`${this.base}/products`, { params, withCredentials: true })
      .pipe(map((res) => res.products.map(mapProduct)));
  }

  // ---- Cart ----

  getCart(): Observable<CartItem[]> {
    return this.http
      .get<{ items: RawCartItem[] }>(`${this.base}/cart`, { withCredentials: true })
      .pipe(map((res) => res.items.map(mapCartItem)));
  }

  addCartItem(productId: number, variantId: number, quantity: number): Observable<CartItem[]> {
    return this.mutate<{ items: RawCartItem[] }>('POST', `${this.base}/cart/items`, {
      product_id: productId,
      variant_id: variantId,
      quantity,
    }).pipe(map((res) => res.items.map(mapCartItem)));
  }

  updateCartItem(itemId: number, quantity: number): Observable<CartItem[]> {
    return this.mutate<{ items: RawCartItem[] }>('PATCH', `${this.base}/cart/items/${itemId}`, {
      quantity,
    }).pipe(map((res) => res.items.map(mapCartItem)));
  }

  removeCartItem(itemId: number): Observable<CartItem[]> {
    return this.mutate<{ items: RawCartItem[] }>(
      'DELETE',
      `${this.base}/cart/items/${itemId}`,
    ).pipe(map((res) => res.items.map(mapCartItem)));
  }

  // ---- Auth ----

  register(name: string, email: string, password: string): Observable<AuthUser> {
    return this.mutate<{ user: AuthUser }>('POST', `${this.base}/auth/register`, {
      name,
      email,
      password,
    }).pipe(map((res) => res.user));
  }

  login(email: string, password: string): Observable<AuthUser> {
    return this.mutate<{ user: AuthUser }>('POST', `${this.base}/auth/login`, {
      email,
      password,
    }).pipe(map((res) => res.user));
  }

  logout(): Observable<void> {
    return this.mutate('POST', `${this.base}/auth/logout`).pipe(map(() => undefined));
  }

  me(): Observable<AuthUser | null> {
    return this.http
      .get<{ user: AuthUser }>(`${this.base}/auth/me`, { withCredentials: true })
      .pipe(
        map((res) => res.user),
        catchError(() => of(null)),
      );
  }

  // ---- Orders ----

  createOrder(shippingAddress: string): Observable<{ id: number; total: string }> {
    return this.mutate('POST', `${this.base}/orders`, { shipping_address: shippingAddress });
  }

  payOrder(orderId: number): Observable<{ payment: unknown }> {
    return this.mutate('POST', `${this.base}/orders/${orderId}/pay`);
  }

  // ---- Internals ----

  private ensureCsrfToken(): Observable<string> {
    if (this.csrfToken) {
      return of(this.csrfToken);
    }
    return this.http
      .get<{ csrf_token: string }>(`${this.base}/csrf-token`, { withCredentials: true })
      .pipe(
        tap((res) => (this.csrfToken = res.csrf_token)),
        map((res) => res.csrf_token),
      );
  }

  private mutate<T>(method: string, url: string, body?: unknown): Observable<T> {
    return this.ensureCsrfToken().pipe(
      switchMap((token) =>
        this.http.request<T>(method, url, {
          body,
          withCredentials: true,
          headers: { 'X-CSRF-Token': token },
        }),
      ),
      catchError((err) => {
        if (err?.status === 419) {
          this.csrfToken = null;
        }
        return throwError(() => err);
      }),
    );
  }
}

function mapProduct(raw: RawProduct): Product {
  const price = Number(raw.price);
  const oldPrice = raw.old_price !== null ? Number(raw.old_price) : undefined;

  return {
    id: raw.id,
    name: raw.name,
    shortDescription: raw.short_description ?? '',
    price,
    oldPrice,
    discount: oldPrice ? Math.round(((oldPrice - price) / oldPrice) * 100) : undefined,
    rating: Number(raw.rating),
    reviewCount: raw.review_count,
    imageUrl: raw.image_url ?? '',
    category: raw.category_name ?? 'Uncategorized',
    isNew: Boolean(raw.is_new),
    isBestSeller: Boolean(raw.is_bestseller),
    variants: raw.variants,
    sizes: [...new Set(raw.variants.map((v) => v.size))],
    colors: [...new Set(raw.variants.map((v) => v.color))],
  };
}

function mapCartItem(raw: RawCartItem): CartItem {
  return {
    id: raw.id,
    quantity: raw.quantity,
    selectedSize: raw.size,
    selectedColor: raw.color,
    stock: raw.stock,
    product: {
      id: raw.product_id,
      name: raw.name,
      price: Number(raw.price),
      imageUrl: raw.image_url ?? '',
    },
  };
}
