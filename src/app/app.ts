import { ChangeDetectionStrategy, Component, computed, signal } from '@angular/core';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { MatIconModule } from '@angular/material/icon';
import { Product, CartItem } from './models';
import { CATEGORIES, PRODUCTS, TESTIMONIALS } from './data';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, CurrencyPipe, MatIconModule],
  templateUrl: './app.html',
  styleUrl: './app.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class App {
  // Data
  categories = CATEGORIES;
  allProducts = PRODUCTS;
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
    this.allProducts.forEach(p => p.sizes.forEach(s => sizes.add(s)));
    return Array.from(sizes).sort();
  });

  availableColors = computed(() => {
    const colors = new Set<string>();
    this.allProducts.forEach(p => p.colors.forEach(c => colors.add(c)));
    return Array.from(colors);
  });

  // Filtered Products
  filteredProducts = computed(() => {
    return this.allProducts.filter(p => {
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
  newArrivals = computed(() => this.allProducts.filter(p => p.isNew).slice(0, 4));
  bestSellers = computed(() => this.allProducts.filter(p => p.isBestSeller).slice(0, 4));
  
  // UI State
  isCartOpen = signal(false);
  isMobileMenuOpen = signal(false);

  // Checkout/View State
  currentView = signal<'home' | 'checkout'>('home');
  selectedProduct = signal<Product | null>(null);

  // Quick View State
  quickViewSize = signal<string>('');
  quickViewColor = signal<string>('');
  quickViewQuantity = signal<number>(1);
  
  // Cart State
  cartItems = signal<CartItem[]>([]);
  
  // Toast State
  toasts = signal<{id: number, message: string}[]>([]);
  private toastIdCounter = 0;
  
  cartTotal = computed(() => {
    return this.cartItems().reduce((total, item) => total + (item.product.price * item.quantity), 0);
  });
  
  cartItemCount = computed(() => {
    return this.cartItems().reduce((count, item) => count + item.quantity, 0);
  });

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
    
    this.cartItems.update(items => {
      const existingItemIndex = items.findIndex(
        i => i.product.id === product.id && 
             i.selectedSize === this.quickViewSize() && 
             i.selectedColor === this.quickViewColor()
      );
      
      if (existingItemIndex > -1) {
        const newItems = [...items];
        newItems[existingItemIndex].quantity += this.quickViewQuantity();
        return newItems;
      }
      return [...items, { 
        product, 
        quantity: this.quickViewQuantity(), 
        selectedSize: this.quickViewSize(), 
        selectedColor: this.quickViewColor() 
      }];
    });
    
    this.showToast(`${product.name} added to cart`);
    this.closeQuickView();
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
    const defaultSize = product.sizes.length > 0 ? product.sizes[0] : '';
    const defaultColor = product.colors.length > 0 ? product.colors[0] : '';
    
    this.cartItems.update(items => {
      const existingItemIndex = items.findIndex(
        i => i.product.id === product.id && i.selectedSize === defaultSize && i.selectedColor === defaultColor
      );

      if (existingItemIndex > -1) {
        const newItems = [...items];
        newItems[existingItemIndex].quantity += 1;
        return newItems;
      }

      return [...items, { product, quantity: 1, selectedSize: defaultSize, selectedColor: defaultColor }];
    });
    
    this.showToast(`${product.name} added to cart`);
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
    this.cartItems.update(items => {
      const newItems = [...items];
      newItems.splice(index, 1);
      return newItems;
    });
  }

  updateQuantity(index: number, delta: number) {
    this.cartItems.update(items => {
      const newItems = [...items];
      const newQuantity = newItems[index].quantity + delta;
      
      if (newQuantity > 0) {
        newItems[index].quantity = newQuantity;
      }
      return newItems;
    });
  }
}

