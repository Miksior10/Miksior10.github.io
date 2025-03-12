-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 12 Mar 2025, 13:44
-- Wersja serwera: 10.4.22-MariaDB
-- Wersja PHP: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `sklep_online`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`) VALUES
(4, 10, 2, 2),
(6, 10, 10, 2);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `shipping_method` varchar(50) NOT NULL,
  `shipping_cost` decimal(10,2) NOT NULL,
  `shipping_address_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `shipping_method`, `shipping_cost`, `shipping_address_id`, `status`, `order_date`) VALUES
(1, 10, '3509.98', 'parcel_locker', '9.99', NULL, 'new', '2025-03-12 12:29:13');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 10, 1, '3499.99');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `status_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `cardholder_name` varchar(255) NOT NULL,
  `card_number` varchar(255) NOT NULL,
  `card_expiry` varchar(255) NOT NULL,
  `card_cvv` varchar(255) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image_url`, `stock`, `created_at`) VALUES
(1, 'iPhone 14 Pro', 'Najnowszy smartfon Apple z doskonałym aparatem', '5999.99', NULL, 10, '2025-03-12 11:06:50'),
(2, 'Samsung Galaxy S23', 'Flagowy smartfon Samsung z ekranem AMOLED', '4499.99', NULL, 15, '2025-03-12 11:06:50'),
(3, 'MacBook Pro M2', 'Laptop Apple z procesorem M2', '7999.99', NULL, 8, '2025-03-12 11:06:50'),
(4, 'PlayStation 5', 'Konsola do gier nowej generacji', '2499.99', NULL, 20, '2025-03-12 11:06:50'),
(5, 'Xbox Series X', 'Najpotężniejsza konsola Microsoft', '2399.99', NULL, 25, '2025-03-12 11:06:50'),
(6, 'Nintendo Switch OLED', 'Przenośna konsola z wyświetlaczem OLED', '1499.99', NULL, 30, '2025-03-12 11:06:50'),
(7, 'AirPods Pro', 'Bezprzewodowe słuchawki z redukcją szumów', '999.99', NULL, 40, '2025-03-12 11:06:50'),
(8, 'iPad Air', 'Lekki i wydajny tablet', '2999.99', NULL, 12, '2025-03-12 11:06:50'),
(9, 'Apple Watch Series 8', 'Smartwatch z funkcjami zdrowotnymi', '1999.99', NULL, 18, '2025-03-12 11:06:50'),
(10, 'DJI Mini 3 Pro', 'Kompaktowy dron z kamerą 4K', '3499.99', NULL, 5, '2025-03-12 11:06:50'),
(11, 'Smartfon Premium', 'Najnowszy model z doskonałym aparatem i wydajnym procesorem', '3999.99', NULL, 15, '2025-03-12 11:14:57'),
(12, 'Laptop Ultra', 'Lekki i wydajny laptop do pracy i rozrywki', '4599.99', NULL, 10, '2025-03-12 11:14:57'),
(13, 'Słuchawki Pro', 'Bezprzewodowe słuchawki z aktywną redukcją szumów', '899.99', NULL, 25, '2025-03-12 11:14:57'),
(14, 'Tablet Max', 'Tablet z wysokiej jakości wyświetlaczem', '2499.99', NULL, 20, '2025-03-12 11:14:57'),
(15, 'Smartwatch Elite', 'Zaawansowany zegarek z monitorem zdrowia', '1299.99', NULL, 30, '2025-03-12 11:14:57'),
(16, 'Kamera Action', 'Wodoodporna kamera sportowa 4K', '999.99', NULL, 18, '2025-03-12 11:14:57'),
(17, 'Głośnik Bluetooth', 'Przenośny głośnik z doskonałym dźwiękiem', '399.99', NULL, 40, '2025-03-12 11:14:57'),
(18, 'Powerbank 20000mAh', 'Pojemny powerbank z szybkim ładowaniem', '199.99', NULL, 50, '2025-03-12 11:14:57'),
(19, 'Mysz Gaming', 'Precyzyjna mysz dla graczy z RGB', '299.99', NULL, 35, '2025-03-12 11:14:57'),
(20, 'Klawiatura Mechaniczna', 'Gamingowa klawiatura z przełącznikami mechanicznymi', '449.99', NULL, 22, '2025-03-12 11:14:57');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `shipping_addresses`
--

CREATE TABLE `shipping_addresses` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(6) NOT NULL,
  `shipping_method` varchar(50) NOT NULL,
  `shipping_point` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `shipping_addresses`
--

INSERT INTO `shipping_addresses` (`id`, `order_id`, `full_name`, `street`, `city`, `postal_code`, `shipping_method`, `shipping_point`) VALUES
(10, 1, 'Michał Nowak', 'Jana Pawła 2', 'Śeiemianowice śląskie', '41-106', '', '41-0001');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `password`, `created_at`, `is_admin`) VALUES
(9, 'admin ', 'admin@sklep.pl', '123456789', '$2y$10$CyTUbYDxl16XHjVIfrqVAOLzFvLJPzxTpdPaRnLfckGIx2VmIWd8a', '2025-03-12 11:36:38', 1),
(10, 'KamilNowak', 'TEST@gmail.com', '999999999', '$2y$10$64Q6gsFomlWncVvU3EPb7uRJyoMZl0zTyzjhVt67BOoVjFZW1SHT.', '2025-03-12 11:40:35', 0);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeksy dla tabeli `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `shipping_address_id` (`shipping_address_id`);

--
-- Indeksy dla tabeli `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeksy dla tabeli `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeksy dla tabeli `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeksy dla tabeli `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT dla tabeli `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT dla tabeli `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT dla tabeli `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT dla tabeli `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT dla tabeli `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ograniczenia dla tabeli `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`shipping_address_id`) REFERENCES `shipping_addresses` (`id`);

--
-- Ograniczenia dla tabeli `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ograniczenia dla tabeli `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Ograniczenia dla tabeli `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Ograniczenia dla tabeli `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD CONSTRAINT `shipping_addresses_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
