export interface ProductVariant {
  id: number;
  size: string;
  color: string;
  stock: number;
}

export interface Product {
  id: number;
  name: string;
  shortDescription: string;
  price: number;
  oldPrice?: number;
  discount?: number;
  rating: number;
  reviewCount: number;
  sizes: string[];
  colors: string[];
  variants: ProductVariant[];
  imageUrl: string;
  category: string;
  isNew?: boolean;
  isBestSeller?: boolean;
}

export interface Category {
  id: number;
  name: string;
  imageUrl: string;
}

export interface Testimonial {
  id: string;
  name: string;
  role: string;
  content: string;
  rating: number;
  imageUrl: string;
}

export interface CartItem {
  id: number;
  product: {
    id: number;
    name: string;
    price: number;
    imageUrl: string;
  };
  quantity: number;
  selectedSize: string;
  selectedColor: string;
  stock: number;
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: string;
}
