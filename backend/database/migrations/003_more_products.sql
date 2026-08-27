-- Adds 10 more catalog products (bringing the total to 16), rounding out
-- each category. Uses name/category lookups instead of hardcoded IDs so it
-- runs safely regardless of row insertion order.
USE aura_ecommerce;

INSERT INTO products (category_id, name, short_description, price, old_price, rating, review_count, image_url, is_new, is_bestseller)
VALUES ((SELECT id FROM categories WHERE name = 'Men'), 'Slim Chino Pants', 'Slim fit pants, ideal for the office or casual events.', 60.00, NULL, 4.4, 78, 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?auto=format&fit=crop&q=80&w=600', 0, 0);

INSERT INTO products (category_id, name, short_description, price, old_price, rating, review_count, image_url, is_new, is_bestseller)
VALUES ((SELECT id FROM categories WHERE name = 'Women'), 'Chunky Knit Sweater', 'Warm and soft sweater, perfect for the winter season.', 75.00, 100.00, 4.9, 156, 'https://images.unsplash.com/photo-1631541909061-71e349d1f203?auto=format&fit=crop&q=80&w=600', 0, 0);

INSERT INTO products (category_id, name, short_description, price, old_price, rating, review_count, image_url, is_new, is_bestseller)
VALUES ((SELECT id FROM categories WHERE name = 'Accessories'), 'Classic Sunglasses', 'UV protection with a timeless design.', 45.00, NULL, 4.5, 302, 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&q=80&w=600', 0, 0);

INSERT INTO products (category_id, name, short_description, price, old_price, rating, review_count, image_url, is_new, is_bestseller)
VALUES ((SELECT id FROM categories WHERE name = 'Footwear'), 'Suede Chelsea Boots', 'Elegance and comfort in every step.', 130.00, NULL, 4.7, 88, 'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&q=80&w=600', 1, 0);

INSERT INTO products (category_id, name, short_description, price, old_price, rating, review_count, image_url, is_new, is_bestseller)
VALUES ((SELECT id FROM categories WHERE name = 'Women'), 'Palazzo Dress Pants', 'Wide leg cut with spectacular drape fabric.', 85.00, NULL, 4.8, 95, 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&q=80&w=600', 0, 0);

INSERT INTO products (category_id, name, short_description, price, old_price, rating, review_count, image_url, is_new, is_bestseller)
VALUES ((SELECT id FROM categories WHERE name = 'Men'), 'Premium Basic T-Shirt', 'Peruvian Pima cotton, ultra soft and durable.', 35.00, NULL, 4.9, 512, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&q=80&w=600', 0, 1);

INSERT INTO products (category_id, name, short_description, price, old_price, rating, review_count, image_url, is_new, is_bestseller)
VALUES ((SELECT id FROM categories WHERE name = 'Accessories'), 'Merino Wool Scarf', 'Essential accessory for the cold, soft and thermal.', 40.00, NULL, 4.6, 45, 'https://images.unsplash.com/photo-1520903920243-00d872a2d1c9?auto=format&fit=crop&q=80&w=600', 0, 0);

INSERT INTO products (category_id, name, short_description, price, old_price, rating, review_count, image_url, is_new, is_bestseller)
VALUES ((SELECT id FROM categories WHERE name = 'Women'), 'Long Wool Coat', 'Impeccable tailored cut for a sophisticated look.', 190.00, 240.00, 4.9, 67, 'https://images.unsplash.com/photo-1539533018447-63fcce2678e3?auto=format&fit=crop&q=80&w=600', 0, 1);

INSERT INTO products (category_id, name, short_description, price, old_price, rating, review_count, image_url, is_new, is_bestseller)
VALUES ((SELECT id FROM categories WHERE name = 'Men'), 'Slim Fit Two-Piece Suit', 'Modern and versatile formal set.', 280.00, NULL, 4.8, 42, 'https://images.unsplash.com/photo-1593030761757-71fae45fa0e7?auto=format&fit=crop&q=80&w=600', 1, 0);

INSERT INTO products (category_id, name, short_description, price, old_price, rating, review_count, image_url, is_new, is_bestseller)
VALUES ((SELECT id FROM categories WHERE name = 'Footwear'), 'Block Heel Sandals', 'Comfort and style for any event.', 85.00, NULL, 4.7, 120, 'https://images.pexels.com/photos/27063080/pexels-photo-27063080.jpeg', 0, 0);

INSERT INTO product_variants (product_id, size, color, stock)
SELECT id, v.size, v.color, v.stock FROM products,
  (SELECT 'M' AS size, '#F5F5DC' AS color, 12 AS stock UNION ALL SELECT '32', '#000080', 15 UNION ALL SELECT '34', '#808080', 10) v
WHERE name = 'Slim Chino Pants';

INSERT INTO product_variants (product_id, size, color, stock)
SELECT id, v.size, v.color, v.stock FROM products,
  (SELECT 'S' AS size, '#E6E6FA' AS color, 6 AS stock UNION ALL SELECT 'M', '#E6E6FA', 8 UNION ALL SELECT 'L', '#C0C0C0', 5) v
WHERE name = 'Chunky Knit Sweater';

INSERT INTO product_variants (product_id, size, color, stock)
SELECT id, v.size, v.color, v.stock FROM products,
  (SELECT 'One Size' AS size, '#000000' AS color, 20 AS stock UNION ALL SELECT 'One Size', '#8B4513', 18) v
WHERE name = 'Classic Sunglasses';

INSERT INTO product_variants (product_id, size, color, stock)
SELECT id, v.size, v.color, v.stock FROM products,
  (SELECT '40' AS size, '#8B4513' AS color, 9 AS stock UNION ALL SELECT '41', '#8B4513', 7 UNION ALL SELECT '42', '#A0522D', 6) v
WHERE name = 'Suede Chelsea Boots';

INSERT INTO product_variants (product_id, size, color, stock)
SELECT id, v.size, v.color, v.stock FROM products,
  (SELECT 'XS' AS size, '#000000' AS color, 4 AS stock UNION ALL SELECT 'S', '#000000', 8 UNION ALL SELECT 'M', '#F5F5DC', 7 UNION ALL SELECT 'L', '#F5F5DC', 5) v
WHERE name = 'Palazzo Dress Pants';

INSERT INTO product_variants (product_id, size, color, stock)
SELECT id, v.size, v.color, v.stock FROM products,
  (SELECT 'S' AS size, '#FFFFFF' AS color, 25 AS stock UNION ALL SELECT 'M', '#FFFFFF', 30 UNION ALL SELECT 'L', '#000000', 22 UNION ALL SELECT 'XL', '#808080', 18) v
WHERE name = 'Premium Basic T-Shirt';

INSERT INTO product_variants (product_id, size, color, stock)
SELECT id, v.size, v.color, v.stock FROM products,
  (SELECT 'One Size' AS size, '#A9A9A9' AS color, 15 AS stock UNION ALL SELECT 'One Size', '#800000', 12) v
WHERE name = 'Merino Wool Scarf';

INSERT INTO product_variants (product_id, size, color, stock)
SELECT id, v.size, v.color, v.stock FROM products,
  (SELECT 'S' AS size, '#000000' AS color, 4 AS stock UNION ALL SELECT 'M', '#000000', 3 UNION ALL SELECT 'L', '#D2B48C', 3) v
WHERE name = 'Long Wool Coat';

INSERT INTO product_variants (product_id, size, color, stock)
SELECT id, v.size, v.color, v.stock FROM products,
  (SELECT '48' AS size, '#000080' AS color, 5 AS stock UNION ALL SELECT '50', '#000080', 6 UNION ALL SELECT '52', '#000000', 4) v
WHERE name = 'Slim Fit Two-Piece Suit';

INSERT INTO product_variants (product_id, size, color, stock)
SELECT id, v.size, v.color, v.stock FROM products,
  (SELECT '36' AS size, '#000000' AS color, 10 AS stock UNION ALL SELECT '37', '#000000', 9 UNION ALL SELECT '38', '#FFD700', 8) v
WHERE name = 'Block Heel Sandals';
