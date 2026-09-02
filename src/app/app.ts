import { ChangeDetectionStrategy, Component, PLATFORM_ID, computed, inject, signal } from '@angular/core';
import { CommonModule, CurrencyPipe, isPlatformBrowser } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatIconModule } from '@angular/material/icon';
import { NavigationEnd, Router, RouterOutlet } from '@angular/router';
import { filter } from 'rxjs/operators';
import { AuthUser, CartItem, Category, Product } from './models';
import { TESTIMONIALS } from './data';
import { ApiService } from './services/api.service';

type LegalPage = 'terms' | 'privacy' | 'returns' | 'withdrawal';

const LEGAL_ROUTE_SLUGS: Record<LegalPage, string> = {
  terms: 'terms-and-conditions',
  privacy: 'privacy-policy',
  returns: 'returns-and-exchanges',
  withdrawal: 'right-of-withdrawal',
};

const LEGAL_PAGE_BY_PATH: Record<string, LegalPage> = Object.fromEntries(
  Object.entries(LEGAL_ROUTE_SLUGS).map(([page, slug]) => [`/${slug}`, page as LegalPage])
);

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, CurrencyPipe, MatIconModule, FormsModule, RouterOutlet],
  templateUrl: './app.html',
  styleUrl: './app.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class App {
  private readonly api = inject(ApiService);
  private readonly platformId = inject(PLATFORM_ID);
  private readonly router = inject(Router);

  // Legal pages (Terms, Privacy, Returns, Right of Withdrawal): rendered as
  // an overlay, but backed by a real route so each has a shareable URL.
  activeLegalPage = signal<LegalPage | null>(null);

  // Data
  categories = signal<Category[]>([]);
  allProducts = signal<Product[]>([]);
  testimonials = TESTIMONIALS;

  // Filter State
  selectedCategory = signal<string>('All');
  maxPrice = signal<number>(300);
  selectedSizes = signal<Set<string>>(new Set());
  selectedColors = signal<Set<string>>(new Set());
  isFilterOpen = signal<boolean>(false);

  // Computed Options
  availableSizes = computed(() => {
    const sizes = new Set<string>();
    this.allProducts().forEach(p => p.sizes.forEach(s => sizes.add(s)));
    return Array.from(sizes).sort();
  });

  availableColors = computed(() => {
    const colors = new Set<string>();
    this.allProducts().forEach(p => p.colors.forEach(c => colors.add(c)));
    return Array.from(colors);
  });

  // Filtered Products
  filteredProducts = computed(() => {
    return this.allProducts().filter(p => {
      const categoryMatch = this.selectedCategory() === 'All' ||
          (this.selectedCategory() === 'New Arrivals' ? p.isNew : p.category === this.selectedCategory());

      const priceMatch = p.price <= this.maxPrice();

      const sizesMatch = this.selectedSizes().size === 0 ||
          p.sizes.some(s => this.selectedSizes().has(s));

      const colorsMatch = this.selectedColors().size === 0 ||
          p.colors.some(c => this.selectedColors().has(c));

      return categoryMatch && priceMatch && sizesMatch && colorsMatch;
    });
  });

  // Computed derivations for home page sections
  newArrivals = computed(() => this.allProducts().filter(p => p.isNew).slice(0, 4));
  bestSellers = computed(() => this.allProducts().filter(p => p.isBestSeller).slice(0, 4));

  // UI State
  isCartOpen = signal(false);
  isMobileMenuOpen = signal(false);
  isAuthModalOpen = signal(false);
  authView = signal<'login' | 'register'>('login');
  authError = signal<string | null>(null);

  // Checkout/View State
  currentView = signal<'home' | 'checkout'>('home');
  selectedProduct = signal<Product | null>(null);

  // Quick View State
  quickViewSize = signal<string>('');
  quickViewColor = signal<string>('');
  quickViewQuantity = signal<number>(1);

  // Cart State (source of truth is the backend)
  cartItems = signal<CartItem[]>([]);

  // Auth State
  currentUser = signal<AuthUser | null>(null);

  // Forms (plain objects for ngModel two-way binding)
  authForm = { name: '', email: '', password: '' };
  checkoutForm = { email: '', firstName: '', lastName: '', address: '', city: '', postalCode: '' };

  // Toast State
  toasts = signal<{id: number, message: string}[]>([]);
  private toastIdCounter = 0;

  cartTotal = computed(() => {
    return this.cartItems().reduce((total, item) => total + (item.product.price * item.quantity), 0);
  });

  cartItemCount = computed(() => {
    return this.cartItems().reduce((count, item) => count + item.quantity, 0);
  });

  constructor() {
    this.router.events.pipe(filter((e) => e instanceof NavigationEnd)).subscribe((e) => {
      const path = (e as NavigationEnd).urlAfterRedirects.split(/[?#]/)[0];
      this.activeLegalPage.set(LEGAL_PAGE_BY_PATH[path] ?? null);
    });

    // The PHP backend isn't available during SSR prerendering:
    // dynamic data is loaded in the browser only, after hydration.
    if (!isPlatformBrowser(this.platformId)) return;

    this.api.getCategories().subscribe(categories => this.categories.set(categories));
    this.api.getProducts().subscribe(products => this.allProducts.set(products));
    this.api.getCart().subscribe(items => this.cartItems.set(items));
    this.api.me().subscribe(user => this.currentUser.set(user));
  }

  // Actions
  goToCheckout() {
    this.currentView.set('checkout');
    this.isCartOpen.set(false);
    window.scrollTo(0, 0);
  }

  goToHome(event?: Event) {
    if (event) event.preventDefault();
    this.currentView.set('home');
    window.scrollTo(0, 0);
  }

  toggleCart() {
    this.isCartOpen.update(v => !v);
  }

  toggleMobileMenu() {
    this.isMobileMenuOpen.update(v => !v);
  }

  // Account / Auth Actions
  onAccountClick() {
    if (this.currentUser()) {
      this.logout();
    } else {
      this.openAuthModal();
    }
  }

  openAuthModal(view: 'login' | 'register' = 'login') {
    this.authView.set(view);
    this.authError.set(null);
    this.authForm = { name: '', email: '', password: '' };
    this.isAuthModalOpen.set(true);
  }

  closeAuthModal() {
    this.isAuthModalOpen.set(false);
  }

  submitAuth() {
    this.authError.set(null);
    const { name, email, password } = this.authForm;

    const request$ = this.authView() === 'register'
      ? this.api.register(name, email, password)
      : this.api.login(email, password);

    request$.subscribe({
      next: (user) => {
        this.currentUser.set(user);
        this.isAuthModalOpen.set(false);
        this.api.getCart().subscribe(items => this.cartItems.set(items));
        this.showToast(`Welcome, ${user.name}`);
      },
      error: (err) => {
        this.authError.set(err?.error?.error ?? 'Could not complete the operation.');
      },
    });
  }

  logout() {
    this.api.logout().subscribe(() => {
      this.currentUser.set(null);
      this.showToast('Logged out.');
    });
  }

  // Quick View Actions
  openQuickView(product: Product) {
    this.selectedProduct.set(product);
    this.quickViewQuantity.set(1);
    this.quickViewSize.set(product.sizes.length > 0 ? product.sizes[0] : '');
    this.quickViewColor.set(product.colors.length > 0 ? product.colors[0] : '');
  }

  closeQuickView() {
    this.selectedProduct.set(null);
  }

  setQuickViewSize(size: string) {
    this.quickViewSize.set(size);
  }

  setQuickViewColor(color: string) {
    this.quickViewColor.set(color);
  }

  updateQuickViewQuantity(delta: number) {
    this.quickViewQuantity.update(q => Math.max(1, q + delta));
  }

  addToCartFromQuickView() {
    const product = this.selectedProduct();
    if (!product) return;

    const variant = product.variants.find(
      v => v.size === this.quickViewSize() && v.color === this.quickViewColor()
    );

    if (!variant) {
      this.showToast('That size and color combination is not available.');
      return;
    }

    this.api.addCartItem(product.id, variant.id, this.quickViewQuantity()).subscribe({
      next: (items) => {
        this.cartItems.set(items);
        this.showToast(`${product.name} added to cart`);
        this.closeQuickView();
      },
      error: () => this.showToast('Could not add the product to the cart.'),
    });
  }

  toggleFilterMobile() {
    this.isFilterOpen.update(v => !v);
  }

  setCategory(cat: string) {
    this.selectedCategory.set(cat);
  }

  updateMaxPrice(event: Event) {
    const value = (event.target as HTMLInputElement).value;
    this.maxPrice.set(Number(value));
  }

  toggleSize(size: string) {
    this.selectedSizes.update(sizes => {
      const newSizes = new Set(sizes);
      if (newSizes.has(size)) {
        newSizes.delete(size);
      } else {
        newSizes.add(size);
      }
      return newSizes;
    });
  }

  toggleColor(color: string) {
    this.selectedColors.update(colors => {
      const newColors = new Set(colors);
      if (newColors.has(color)) {
        newColors.delete(color);
      } else {
        newColors.add(color);
      }
      return newColors;
    });
  }

  clearFilters() {
    this.maxPrice.set(300);
    this.selectedSizes.set(new Set());
    this.selectedColors.set(new Set());
    this.selectedCategory.set('All');
  }

  addToCart(product: Product) {
    const variant = product.variants[0];
    if (!variant) {
      this.showToast('This product has no available variants.');
      return;
    }

    this.api.addCartItem(product.id, variant.id, 1).subscribe({
      next: (items) => {
        this.cartItems.set(items);
        this.showToast(`${product.name} added to cart`);
      },
      error: () => this.showToast('Could not add the product to the cart.'),
    });
  }

  showToast(message: string) {
    const id = this.toastIdCounter++;
    this.toasts.update(t => [...t, { id, message }]);
    setTimeout(() => {
      this.removeToast(id);
    }, 3000);
  }

  removeToast(id: number) {
    this.toasts.update(t => t.filter(toast => toast.id !== id));
  }

  removeFromCart(index: number) {
    const item = this.cartItems()[index];
    if (!item) return;

    this.api.removeCartItem(item.id).subscribe({
      next: (items) => this.cartItems.set(items),
      error: () => this.showToast('Could not remove the product from the cart.'),
    });
  }

  updateQuantity(index: number, delta: number) {
    const item = this.cartItems()[index];
    if (!item) return;

    const newQuantity = item.quantity + delta;
    if (newQuantity < 1) return;

    this.api.updateCartItem(item.id, newQuantity).subscribe({
      next: (items) => this.cartItems.set(items),
      error: () => this.showToast('Could not update the quantity.'),
    });
  }

  placeOrder() {
    if (!this.currentUser()) {
      this.showToast('Log in to complete your purchase.');
      this.openAuthModal('login');
      return;
    }

    const { firstName, lastName, address, city, postalCode } = this.checkoutForm;
    const shippingAddress = `${firstName} ${lastName}, ${address}, ${city}, ${postalCode}`.trim();

    if (!address || !city) {
      this.showToast('Please complete the shipping address.');
      return;
    }

    this.api.createOrder(shippingAddress).subscribe({
      next: (order) => {
        this.api.payOrder(order.id).subscribe(() => {
          this.cartItems.set([]);
          this.showToast('Order placed successfully!');
          this.goToHome();
        });
      },
      error: (err) => {
        this.showToast(err?.error?.error ?? 'Could not complete the order.');
      },
    });
  }

  // Legal Pages
  openLegalPage(page: LegalPage, event?: Event) {
    if (event) event.preventDefault();
    this.router.navigateByUrl(`/${LEGAL_ROUTE_SLUGS[page]}`);
  }

  closeLegalPage() {
    this.router.navigateByUrl('/');
  }
}
