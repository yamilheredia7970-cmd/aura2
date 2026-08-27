-- Traduce al español el contenido de categorías y productos cargado por 002_seed.sql.
-- Seguro de ejecutar sobre una base ya poblada: solo actualiza texto, no toca IDs ni relaciones.
USE aura_ecommerce;

UPDATE categories SET name = 'Mujer' WHERE name = 'Women';
UPDATE categories SET name = 'Hombre' WHERE name = 'Men';
UPDATE categories SET name = 'Calzado' WHERE name = 'Footwear';
UPDATE categories SET name = 'Accesorios' WHERE name = 'Accessories';

UPDATE products SET name = 'Blazer de Lino Elegante', short_description = 'Blazer de lino liviano premium con corte entallado.' WHERE name = 'Elegant Linen Blazer';
UPDATE products SET name = 'Camisa Oxford Clásica', short_description = 'Camisa 100% algodón, ideal para el trabajo o salidas casuales.' WHERE name = 'Classic Oxford Shirt';
UPDATE products SET name = 'Vestido Midi Estampado', short_description = 'Vestido suelto con estampado floral y ajuste en la cintura.' WHERE name = 'Printed Midi Dress';
UPDATE products SET name = 'Campera de Cuero Vintage', short_description = 'Campera de cuero genuino con estilo motero clásico.' WHERE name = 'Vintage Leather Jacket';
UPDATE products SET name = 'Zapatillas Urbanas Blancas', short_description = 'Comodidad diaria con un diseño minimalista y versátil.' WHERE name = 'White Urban Sneakers';
UPDATE products SET name = 'Bolso Tote de Cuero Vegano', short_description = 'Bolso espacioso para el día a día, elegante y durable.' WHERE name = 'Vegan Leather Tote Bag';
