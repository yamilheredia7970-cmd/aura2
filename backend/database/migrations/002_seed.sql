USE aura_ecommerce;

INSERT INTO categories (name, image_url) VALUES
('Women', 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&q=80&w=800'),
('Men', 'https://images.unsplash.com/photo-1516257984-b1b4d707412e?auto=format&fit=crop&q=80&w=800'),
('Footwear', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&q=80&w=800'),
('Accessories', 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?auto=format&fit=crop&q=80&w=800');

INSERT INTO products (category_id, name, short_description, price, old_price, rating, review_count, image_url, is_new, is_bestseller) VALUES
(1, 'Elegant Linen Blazer', 'Premium lightweight linen blazer with a tailored fit.', 120.00, 150.00, 4.8, 124, 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&q=80&w=600', 0, 1),
(2, 'Classic Oxford Shirt', '100% cotton shirt ideal for work or casual outings.', 55.00, NULL, 4.5, 89, 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?auto=format&fit=crop&q=80&w=600', 1, 0),
(1, 'Printed Midi Dress', 'Flowy dress with floral print and waist adjustment.', 85.00, NULL, 4.9, 210, 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?auto=format&fit=crop&q=80&w=600', 0, 1),
(2, 'Vintage Leather Jacket', 'Genuine leather jacket with classic biker style.', 250.00, NULL, 4.7, 56, 'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&q=80&w=600', 0, 0),
(3, 'White Urban Sneakers', 'Daily comfort with a minimalist and versatile design.', 95.00, 110.00, 4.6, 340, 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&q=80&w=600', 0, 1),
(4, 'Vegan Leather Tote Bag', 'Spacious everyday bag, elegant and durable.', 70.00, NULL, 4.8, 112, 'https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&q=80&w=600', 1, 0);

-- Variantes (talla/color/stock) para cada producto insertado arriba
INSERT INTO product_variants (product_id, size, color, stock) VALUES
(1, 'S', '#F5F5DC', 5), (1, 'M', '#F5F5DC', 6), (1, 'L', '#000000', 4),
(2, 'M', '#FFFFFF', 10), (2, 'L', '#ADD8E6', 10), (2, 'XL', '#FFC0CB', 10),
(3, 'XS', '#800000', 2), (3, 'S', '#000080', 3),
(4, 'M', '#8B4513', 3), (4, 'L', '#000000', 5),
(5, '39', '#FFFFFF', 15), (5, '40', '#FFFFFF', 15), (5, '41', '#FFFFFF', 15),
(6, 'One Size', '#D2B48C', 20);
