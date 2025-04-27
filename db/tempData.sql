-- Insert data into categories table (10 records)
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Electronics'),
(2, 'Clothing'),
(3, 'Home & Kitchen'),
(4, 'Books'),
(5, 'Sports & Outdoors'),
(6, 'Toys & Games'),
(7, 'Beauty & Personal Care'),
(8, 'Automotive'),
(9, 'Garden & Outdoor'),
(10, 'Pet Supplies');

-- Insert data into users table (10 records)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `profile_pic`, `role`, `created_at`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin.jpg', 'admin', '2025-01-15 08:00:00'),
(2, 'John Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john.jpg', 'user', '2025-02-10 10:30:00'),
(3, 'Jane Smith', 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jane.jpg', 'user', '2025-03-05 14:15:00'),
(4, 'Mike Johnson', 'mike.johnson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'user', '2025-03-20 09:45:00'),
(5, 'Sarah Williams', 'sarah.williams@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sarah.jpg', 'user', '2025-04-01 16:20:00'),
(6, 'David Brown', 'david.brown@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'david.jpg', 'user', '2025-04-05 12:10:00'),
(7, 'Emily Davis', 'emily.davis@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'emily.jpg', 'user', '2025-04-08 15:30:00'),
(8, 'Robert Wilson', 'robert.wilson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'user', '2025-04-10 09:20:00'),
(9, 'Lisa Taylor', 'lisa.taylor@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lisa.jpg', 'user', '2025-04-12 11:45:00'),
(10, 'Thomas Anderson', 'thomas.anderson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'thomas.jpg', 'user', '2025-04-15 14:00:00');

-- Insert data into products table (20 records)
INSERT INTO `products` (`id`, `name`, `price`, `image`, `category_id`) VALUES
(1, 'Smartphone X', 799.99, 'phone.jpg', 1),
(2, 'Wireless Headphones', 149.99, 'headphones.jpg', 1),
(3, 'Cotton T-Shirt', 24.99, 'tshirt.jpg', 2),
(4, 'Jeans', 59.99, 'jeans.jpg', 2),
(5, 'Coffee Maker', 89.99, 'coffee.jpg', 3),
(6, 'Blender', 49.99, 'blender.jpg', 3),
(7, 'Bestseller Novel', 14.99, 'book.jpg', 4),
(8, 'Programming Guide', 29.99, 'programming.jpg', 4),
(9, 'Yoga Mat', 34.99, 'yoga.jpg', 5),
(10, 'Running Shoes', 99.99, 'shoes.jpg', 5),
(11, 'Smart Watch', 199.99, 'watch.jpg', 1),
(12, 'Leather Jacket', 149.99, 'jacket.jpg', 2),
(13, 'Air Fryer', 99.99, 'airfryer.jpg', 3),
(14, 'Cookware Set', 124.99, 'cookware.jpg', 3),
(15, 'Science Fiction Book', 19.99, 'scifi.jpg', 4),
(16, 'History Book', 22.99, 'history.jpg', 4),
(17, 'Dumbbell Set', 49.99, 'dumbbells.jpg', 5),
(18, 'Basketball', 29.99, 'basketball.jpg', 5),
(19, 'Board Game', 39.99, 'boardgame.jpg', 6),
(20, 'Skincare Set', 59.99, 'skincare.jpg', 7);

-- Insert data into orders table (10 records)
INSERT INTO `orders` (`id`, `user_id`, `notes`, `total`, `status`, `created_at`) VALUES
(1, 2, 'Please deliver after 5pm', 824.98, 'done', '2025-02-15 11:30:00'),
(2, 3, 'Gift wrapping required', 74.98, 'out for delivery', '2025-03-10 14:45:00'),
(3, 2, NULL, 149.99, 'processing', '2025-03-25 09:15:00'),
(4, 4, 'Leave at front door', 189.98, 'processing', '2025-04-05 16:20:00'),
(5, 5, 'Birthday present', 134.97, 'out for delivery', '2025-04-15 10:00:00'),
(6, 7, 'Fragile items', 299.97, 'processing', '2025-04-18 08:30:00'),
(7, 8, 'Office delivery', 174.98, 'done', '2025-04-19 11:20:00'),
(8, 9, 'Contact before delivery', 89.99, 'out for delivery', '2025-04-20 14:10:00'),
(9, 10, 'Weekend delivery only', 224.97, 'processing', '2025-04-21 09:45:00'),
(10, 6, 'Neighbor can accept', 149.99, 'cancelled', '2025-04-22 16:55:00');

-- Insert data into order_products table (20 records)
INSERT INTO `order_products` (`id`, `order_id`, `product_id`, `quantity`) VALUES
(1, 1, 1, 1),
(2, 1, 3, 1),
(3, 2, 3, 2),
(4, 2, 7, 1),
(5, 3, 2, 1),
(6, 4, 5, 1),
(7, 4, 6, 1),
(8, 5, 9, 1),
(9, 5, 3, 2),
(10, 5, 7, 1),
(11, 6, 11, 1),
(12, 6, 13, 2),
(13, 7, 14, 1),
(14, 7, 17, 1),
(15, 8, 15, 1),
(16, 9, 19, 3),
(17, 10, 12, 1),
(18, 6, 18, 1),
(19, 7, 20, 1),
(20, 8, 16, 1);