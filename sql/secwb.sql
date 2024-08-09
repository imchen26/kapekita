-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 08, 2024 at 05:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `secwb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`, `created_at`) VALUES
(1, 'admin@example.com', '$2y$10$WzV.xP0My1s8hFHqNIdFf.kpHQyW5od9I60i3mCAs5oqt0jFgXOGa', '2024-06-05 05:47:30'),
(2, 'admin@test.com', '$2y$10$NvNOvyWdOYKDzGSRWpOA6OeZZwz.6HSJQq2QaZhaT91LagIWdQOYy', '2024-06-08 06:25:30');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `author_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `title`, `content`, `author_id`, `author_name`, `created_at`, `image`) VALUES
(1, 'Top 5 Coffee Recipes', 'Discover our favorite coffee recipes that you can easily make at home!', 4, 'Juan Dela Cruz', '2024-07-15 17:00:00', 'blog/building.jpg'),
(2, 'History of Coffee', 'Explore the rich history and cultural significance of coffee around the world.', 5, 'Maria Santos', '2024-07-15 18:30:00', 'blog/coffee.jpg'),
(3, 'Coffee Tasting Tips', 'Learn how to taste coffee like a pro with these expert tips.', 6, 'Pedro Reyes', '2024-07-15 19:45:00', 'blog/coffeeshop.jpg'),
(4, 'Coffee Shop Events', 'Stay updated on our upcoming events and promotions at the coffee shop.', 7, 'Juan Dela Cruz', '2024-07-15 21:00:00', 'blog/shop.jpg'),
(5, 'Coffee and Health', 'Discover the health benefits and considerations of drinking coffee.', 9, 'Sofia Cruz', '2024-07-15 22:15:00', 'blog/store.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `description`, `price`, `image_path`, `created_at`, `updated_at`, `quantity`) VALUES
(11, 'Espresso', 'Strong and bold coffee, perfect for a quick boost.', 2.50, 'menu/espresso.jpg', '2024-01-01 02:00:00', '2024-01-01 02:00:00', 100),
(12, 'Latte', 'Smooth and creamy coffee with steamed milk.', 3.50, 'menu/latte.jpg', '2024-01-02 03:00:00', '2024-01-02 03:00:00', 150),
(13, 'Cappuccino', 'Rich coffee with a layer of frothy milk.', 3.00, 'menu/cappuccino.jpg', '2024-01-03 04:00:00', '2024-01-03 04:00:00', 120),
(14, 'Americano', 'Espresso with added hot water for a lighter flavor.', 2.75, 'menu/americano.jpg', '2024-01-04 05:00:00', '2024-01-04 05:00:00', 80),
(15, 'Mocha', 'Coffee with chocolate syrup and steamed milk.', 4.00, 'menu/mocha.jpg', '2024-01-05 06:00:00', '2024-01-05 06:00:00', 90),
(16, 'Flat White', 'Velvety coffee with microfoam milk.', 3.25, 'menu/flat_white.jpg', '2024-01-06 07:00:00', '2024-01-06 07:00:00', 110),
(17, 'Macchiato', 'Espresso with a small amount of steamed milk.', 3.00, 'menu/macchiato.jpg', '2024-01-07 08:00:00', '2024-01-07 08:00:00', 100),
(18, 'Iced Coffee', 'Chilled coffee with ice, perfect for hot days.', 2.50, 'menu/iced_coffee.jpg', '2024-01-08 09:00:00', '2024-01-08 09:00:00', 120),
(19, 'Cold Brew', 'Smooth and refreshing cold brewed coffee.', 3.00, 'menu/cold_brew.jpg', '2024-01-09 10:00:00', '2024-01-09 10:00:00', 130),
(20, 'Frappuccino', 'Blended coffee with ice and whipped cream.', 4.50, 'menu/frappuccino.jpg', '2024-01-10 11:00:00', '2024-01-10 11:00:00', 100),
(21, 'Croissant', 'Buttery and flaky French pastry.', 2.00, 'menu/croissant.jpg', '2024-01-01 02:00:00', '2024-01-01 02:00:00', 50),
(22, 'Blueberry Muffin', 'Moist muffin loaded with blueberries.', 2.50, 'menu/blueberry_muffin.jpg', '2024-01-02 03:00:00', '2024-01-02 03:00:00', 40),
(23, 'Bagel with Cream Cheese', 'Freshly baked bagel served with cream cheese.', 3.00, 'menu/bagel_cream_cheese.jpg', '2024-01-03 04:00:00', '2024-01-03 04:00:00', 60),
(24, 'Chocolate Chip Cookie', 'Chewy cookie with chocolate chips.', 1.50, 'menu/chocolate_chip_cookie.jpg', '2024-01-04 05:00:00', '2024-01-04 05:00:00', 70),
(25, 'Banana Bread', 'Moist and flavorful banana bread.', 2.75, 'menu/banana_bread.jpg', '2024-01-05 06:00:00', '2024-01-05 06:00:00', 30),
(26, 'Ham and Cheese Sandwich', 'Classic sandwich with ham and cheese.', 4.00, 'menu/ham_cheese_sandwich.jpg', '2024-01-06 07:00:00', '2024-01-06 07:00:00', 20),
(27, 'Quiche Lorraine', 'Savory quiche with bacon, cheese, and onions.', 3.50, 'menu/quiche_lorraine.jpg', '2024-01-07 08:00:00', '2024-01-07 08:00:00', 25),
(28, 'Fruit Tart', 'Delicious tart topped with fresh fruits.', 3.00, 'menu/fruit_tart.jpg', '2024-01-08 09:00:00', '2024-01-08 09:00:00', 35),
(29, 'Cinnamon Roll', 'Soft roll with cinnamon and icing.', 2.50, 'menu/cinnamon_roll.jpg', '2024-01-09 10:00:00', '2024-01-09 10:00:00', 45),
(30, 'Greek Yogurt Parfait', 'Healthy parfait with yogurt and fruits.', 3.75, 'menu/greek_yogurt_parfait.jpg', '2024-01-10 11:00:00', '2024-01-10 11:00:00', 55),
(31, 'Scone', 'British-style scone with a crumbly texture.', 2.75, 'menu/scone.jpg', '2024-07-16 15:16:04', '2024-07-16 15:37:53', 40);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 31.50, 'completed', '2024-07-17 02:30:00', '2024-07-16 18:26:14'),
(2, 2, 13.00, 'pending', '2024-07-17 03:15:00', '2024-07-16 18:24:55'),
(3, 3, 44.75, 'completed', '2024-07-17 04:00:00', '2024-07-16 18:26:14'),
(4, 1, 18.00, 'pending', '2024-07-17 05:30:00', '2024-07-16 18:26:14'),
(5, 2, 12.75, 'completed', '2024-07-17 06:45:00', '2024-07-16 18:26:14'),
(6, 4, 19.50, 'completed', '2024-07-17 07:30:00', '2024-07-16 18:26:14'),
(7, 5, 44.25, 'pending', '2024-07-17 08:45:00', '2024-07-16 18:26:14'),
(8, 6, 15.50, 'completed', '2024-07-17 09:30:00', '2024-07-16 18:26:14'),
(9, 7, 14.75, 'completed', '2024-07-17 10:45:00', '2024-07-18 10:26:26'),
(10, 9, 35.00, 'completed', '2024-07-17 11:30:00', '2024-07-16 18:26:14'),
(11, 7, 32.00, 'pending', '2024-07-18 10:43:44', '2024-07-18 10:43:44'),
(12, 10, 18.50, 'completed', '2024-08-08 15:13:49', '2024-08-08 15:13:49');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_id`, `quantity`, `price`) VALUES
(1, 1, 11, 2, 5.00),
(2, 1, 12, 1, 3.50),
(3, 2, 13, 1, 3.00),
(4, 3, 14, 3, 8.25),
(5, 3, 15, 1, 4.00),
(6, 3, 16, 2, 6.50),
(7, 4, 17, 2, 6.00),
(8, 5, 18, 1, 2.50),
(9, 5, 19, 1, 3.00),
(10, 5, 20, 1, 4.50),
(11, 1, 21, 3, 6.00),
(12, 2, 22, 2, 5.00),
(13, 3, 23, 1, 3.00),
(14, 4, 24, 2, 3.00),
(15, 5, 25, 1, 2.75),
(16, 6, 26, 2, 7.50),
(17, 6, 27, 1, 4.50),
(18, 7, 28, 3, 9.75),
(19, 7, 29, 1, 3.00),
(20, 7, 30, 2, 6.00),
(21, 8, 31, 2, 5.00),
(22, 8, 26, 1, 2.50),
(23, 8, 27, 1, 3.00),
(24, 9, 28, 2, 6.00),
(25, 9, 29, 1, 2.75),
(26, 10, 30, 1, 3.50),
(27, 10, 31, 3, 10.50),
(28, 11, 11, 2, 5.00),
(29, 11, 15, 1, 4.50),
(30, 11, 21, 3, 6.00);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `author_name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `rating` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `author_name`, `title`, `content`, `rating`, `created_at`) VALUES
(1, 4, 'Juan Dela Cruz', 'Great Coffee!', 'I loved the coffee here. It was fresh and delicious.', 5, '2024-07-17 02:00:00'),
(2, 5, 'Maria Clara', 'Nice ambiance', 'The ambiance of the coffee shop is cozy and welcoming.', 4, '2024-07-17 03:30:00'),
(3, 6, 'Jose Rizal', 'Good service', 'The service was quick and friendly.', 4, '2024-07-17 04:45:00'),
(4, 7, 'Andres Bonifacio', 'Average experience', 'The coffee was okay, nothing special.', 3, '2024-07-17 06:00:00'),
(5, 9, 'Emilio Aguinaldo', 'Excellent place!', 'Highly recommend this coffee shop for its quality.', 5, '2024-07-17 07:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(300) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `wallet` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone_number`, `profile_photo`, `password`, `created_at`, `wallet`) VALUES
(4, 'hi', 'hello@gmail.com', '09128308244', 'hello.jpg', '$2y$10$/vaV7LcH3Spw8NQFQq2FJ.vWKli4uz.2RUw9PpOD8hfA0tuMDTRT2', '2024-06-10 08:37:41', 100.00),
(5, 'hm', 'hm@gmail.com', '09324082080', 'scone.jpg', '$2y$10$lji1ZU5tVEsW.hqQDQgTL.ubHyAr3omhGYQMYfcnBqNKyDS3KlUKq', '2024-06-11 09:02:12', 50.00),
(6, 'good', 'good12@gmail.com', '+637565564645', '1.png', '$2y$10$4gBSoZo1GHcR7S3C6jn.GemAdR7NGKnq65BswI.3qKvTMRuBmka0e', '2024-06-11 09:03:17', 75.00),
(7, 'example', 'example@gmail.com', '+637565564365', '1.png', '$2y$10$PG2FoomaICVwqa2m5fj8Cu86b2GYllH39V0Yz2203dEq.p0qk5EFa', '2024-06-11 09:11:28', 1246.27),
(9, 'girl', 'user@gmail.com', '+639128307299', 'girl.jpg', '$2y$10$pk6WH4Q/no/oRSRcAREh9uqGCCBRksU.A7XM.uqPu6Al64DRgptjO', '2024-06-13 05:31:54', 150.00),
(10, 'testing', 'testing@gmail.com', '+639128308244', '1.jpg', '$2y$10$c1UkJhs4UhlwZbE4PsoyBeGtcwNRpcpS3A261rsjGDSkRI6xmFBa6', '2024-06-14 04:27:53', 1300.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
