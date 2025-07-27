-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2025 at 03:13 PM
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
-- Database: `booknest`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddBook` (IN `p_title` VARCHAR(255), IN `p_author` VARCHAR(255), IN `p_price` DECIMAL(10,2), IN `p_stock` INT, IN `p_category_id` INT, IN `p_is_featured` BOOLEAN, IN `p_is_digital` BOOLEAN)   BEGIN
    INSERT INTO books (
        title, author, price, stock, category_id, is_featured, is_digital
    ) VALUES (
        p_title, p_author, p_price, p_stock, p_category_id, p_is_featured, p_is_digital
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `PlaceOrder` (IN `p_user_id` INT)   BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE c_book_id INT;
    DECLARE c_quantity INT;
    DECLARE c_price DECIMAL(10,2);
    DECLARE order_id INT;
    
    DECLARE cur CURSOR FOR
        SELECT book_id, quantity, (SELECT price FROM books WHERE book_id = c.book_id)
        FROM cart c WHERE user_id = p_user_id;
        
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    START TRANSACTION;
    
    INSERT INTO orders(user_id) VALUES(p_user_id);
    SET order_id = LAST_INSERT_ID();
    
    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO c_book_id, c_quantity, c_price;
        IF done THEN
            LEAVE read_loop;
        END IF;
        INSERT INTO order_items(order_id, book_id, quantity, price)
        VALUES(order_id, c_book_id, c_quantity, c_price);
        UPDATE books SET stock = stock - c_quantity WHERE book_id = c_book_id;
    END LOOP;
    CLOSE cur;
    
    DELETE FROM cart WHERE user_id = p_user_id;
    
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateBookStock` (IN `p_book_id` INT, IN `p_stock` INT)   BEGIN
    UPDATE books SET stock = p_stock WHERE book_id = p_book_id;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `ConvertPrice` (`p_price` DECIMAL(10,2), `p_user_id` INT) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    DECLARE rate DECIMAL(10,4);

    SELECT c.exchange_rate
    INTO rate
    FROM `user_currency_preference` AS ucp
    JOIN `currencies` AS c ON ucp.currency_id = c.currency_id
    WHERE ucp.user_id = p_user_id;

    RETURN p_price * IFNULL(rate, 1);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_digital` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `author`, `price`, `stock`, `category_id`, `is_featured`, `is_digital`) VALUES
(1, 'The Alchemist', 'Paulo Coelho', 499.00, 10, 1, 1, 0),
(2, 'A Brief History of Time', 'Stephen Hawking', 650.00, 8, 3, 0, 0),
(3, 'Rich Dad Poor Dad', 'Robert Kiyosaki', 550.00, 12, 5, 1, 0),
(4, 'Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', 450.00, 15, 4, 1, 0),
(5, 'The Lean Startup', 'Eric Ries', 720.00, 7, 5, 0, 0),
(6, 'Sherlock Holmes', 'Arthur Conan Doyle', 380.00, 5, 6, 0, 0),
(7, 'The Silent Patient', 'Alex Michaelides', 499.00, 10, 2, 1, 0),
(8, 'Atomic Habits', 'James Clear', 599.00, 15, 3, 1, 1),
(9, 'It Ends With Us', 'Colleen Hoover', 429.00, 20, 4, 0, 0),
(10, 'Rich Dad Poor Dad', 'Robert T. Kiyosaki', 550.00, 12, 3, 0, 1),
(11, 'The Psychology of Money', 'Morgan Housel', 470.00, 8, 3, 0, 0),
(12, 'The Hobbit', 'J.R.R. Tolkien', 620.00, 5, 1, 1, 1),
(13, 'To Kill a Mockingbird', 'Harper Lee', 450.00, 7, 2, 0, 0),
(14, '1984', 'George Orwell', 390.00, 6, 2, 0, 1),
(15, 'Educated', 'Tara Westover', 520.00, 9, 4, 1, 0),
(16, 'Dune', 'Frank Herbert', 699.00, 4, 1, 1, 1),
(17, 'The Notebook', 'Nicholas Sparks', 480.00, 10, 7, 1, 0),
(18, 'Dracula', 'Bram Stoker', 399.00, 6, 8, 0, 1),
(19, 'Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', 750.00, 8, 9, 1, 0),
(20, 'Steve Jobs', 'Walter Isaacson', 820.00, 5, 10, 0, 0),
(21, 'The Name of the Wind', 'Patrick Rothfuss', 699.00, 7, 11, 1, 1),
(22, 'The Adventures of Huckleberry Finn', 'Mark Twain', 460.00, 9, 12, 0, 0),
(23, 'The Power of Now', 'Eckhart Tolle', 520.00, 6, 13, 1, 1),
(24, 'Milk and Honey', 'Rupi Kaur', 350.00, 15, 14, 1, 1),
(25, 'Humans of New York', 'Brandon Stanton', 780.00, 4, 15, 0, 0);

--
-- Triggers `books`
--
DELIMITER $$
CREATE TRIGGER `after_update_book_stock` AFTER UPDATE ON `books` FOR EACH ROW BEGIN
  IF NOT OLD.stock <=> NEW.stock THEN
    INSERT INTO `book_stock_log` (`book_id`, `title`, `old_stock`, `new_stock`, `updated_at`)
    VALUES (NEW.book_id, NEW.title, OLD.stock, NEW.stock, NOW());
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `book_id`, `quantity`) VALUES
(4, 7, 1, 2),
(5, 7, 3, 1),
(6, 8, 4, 1),
(7, 8, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(1, 'Fiction'),
(2, 'Non-Fiction'),
(3, 'Science'),
(4, 'Children'),
(5, 'Business'),
(6, 'Mystery'),
(7, 'Romance'),
(8, 'Horror'),
(9, 'History'),
(10, 'Biography'),
(11, 'Fantasy'),
(12, 'Adventure'),
(13, 'Self-Help'),
(14, 'Poetry'),
(15, 'Art & Photography');

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `currency_id` int(11) NOT NULL,
  `currency_code` varchar(10) NOT NULL,
  `exchange_rate` decimal(10,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`currency_id`, `currency_code`, `exchange_rate`) VALUES
(1, 'PHP', 1.0000),
(2, 'USD', 0.0180),
(3, 'KRW', 23.5000);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `action` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `action`, `description`, `created_at`) VALUES
(1, 'SIGNUP', 'New user admin1 registered.', '2025-07-17 12:20:48'),
(2, 'SIGNUP', 'New user staff1 registered.', '2025-07-17 12:20:48'),
(3, 'SIGNUP', 'New user cust1 registered.', '2025-07-17 12:20:48'),
(4, 'SIGNUP', 'New user cust2 registered.', '2025-07-17 12:20:48');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','processing','shipped','completed','cancelled') DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `status`, `total_amount`) VALUES
(1, 7, '2025-07-17 05:10:00', 'pending', 1049.00),
(2, 8, '2025-07-17 05:12:00', 'pending', 1100.00),
(9, 7, '2025-07-17 14:01:47', 'pending', 650.00),
(11, 7, '2025-07-25 04:43:48', 'pending', 650.00),
(12, 7, '2025-07-25 04:45:01', 'pending', 550.00);

--
-- Triggers `orders`
--
DELIMITER $$
CREATE TRIGGER `after_order_insert` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
  INSERT INTO `transaction_log` (
    `order_id`,
    `payment_method`,
    `payment_status`,
    `amount`,
    `timestamp`
  )
  VALUES (
    NEW.order_id,
    'Cash',
    'Pending',
    NEW.total_amount,
    NOW()
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `book_id`, `quantity`, `subtotal`) VALUES
(1, 1, 1, 2, 998.00),
(2, 1, 3, 1, 550.00),
(3, 2, 2, 1, 650.00),
(4, 2, 4, 1, 450.00),
(8, 9, 2, 1, 650.00),
(9, 11, 2, 1, 650.00),
(10, 12, 3, 1, 550.00);

--
-- Triggers `order_items`
--
DELIMITER $$
CREATE TRIGGER `trg_reduce_stock` BEFORE INSERT ON `order_items` FOR EACH ROW BEGIN
    IF (SELECT stock FROM books WHERE book_id = NEW.book_id) < NEW.quantity THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Not enough stock';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `book_id`, `rating`, `created_at`) VALUES
(1, 1, 5, '2025-07-27 12:53:13'),
(2, 1, 4, '2025-07-27 12:53:13'),
(3, 2, 3, '2025-07-27 12:53:13'),
(4, 3, 5, '2025-07-27 12:53:13'),
(5, 4, 4, '2025-07-27 12:53:13'),
(6, 4, 5, '2025-07-27 12:53:13'),
(7, 1, 5, '2025-07-27 13:12:39'),
(8, 1, 4, '2025-07-27 13:12:39'),
(9, 1, 3, '2025-07-27 13:12:39'),
(10, 2, 4, '2025-07-27 13:12:39'),
(11, 2, 5, '2025-07-27 13:12:39'),
(12, 2, 2, '2025-07-27 13:12:39'),
(13, 3, 5, '2025-07-27 13:12:39'),
(14, 3, 4, '2025-07-27 13:12:39'),
(15, 4, 5, '2025-07-27 13:12:39'),
(16, 4, 5, '2025-07-27 13:12:39'),
(17, 5, 3, '2025-07-27 13:12:39'),
(18, 5, 4, '2025-07-27 13:12:39'),
(19, 6, 5, '2025-07-27 13:12:39'),
(20, 7, 4, '2025-07-27 13:12:39'),
(21, 7, 5, '2025-07-27 13:12:39'),
(22, 1, 5, '2025-07-27 13:13:41'),
(23, 1, 4, '2025-07-27 13:13:41'),
(24, 1, 3, '2025-07-27 13:13:41'),
(25, 1, 5, '2025-07-27 13:13:41'),
(26, 2, 4, '2025-07-27 13:13:41'),
(27, 2, 5, '2025-07-27 13:13:41'),
(28, 2, 3, '2025-07-27 13:13:41'),
(29, 3, 5, '2025-07-27 13:13:41'),
(30, 3, 4, '2025-07-27 13:13:41'),
(31, 3, 5, '2025-07-27 13:13:41'),
(32, 3, 5, '2025-07-27 13:13:41'),
(33, 4, 5, '2025-07-27 13:13:41'),
(34, 4, 4, '2025-07-27 13:13:41'),
(35, 4, 5, '2025-07-27 13:13:41'),
(36, 5, 3, '2025-07-27 13:13:41'),
(37, 5, 4, '2025-07-27 13:13:41'),
(38, 5, 4, '2025-07-27 13:13:41'),
(39, 6, 5, '2025-07-27 13:13:41'),
(40, 6, 4, '2025-07-27 13:13:41'),
(41, 6, 3, '2025-07-27 13:13:41'),
(42, 7, 4, '2025-07-27 13:13:41'),
(43, 7, 5, '2025-07-27 13:13:41'),
(44, 7, 4, '2025-07-27 13:13:41'),
(45, 8, 5, '2025-07-27 13:13:41'),
(46, 8, 4, '2025-07-27 13:13:41'),
(47, 8, 5, '2025-07-27 13:13:41'),
(48, 8, 5, '2025-07-27 13:13:41'),
(49, 9, 4, '2025-07-27 13:13:41'),
(50, 9, 5, '2025-07-27 13:13:41'),
(51, 9, 3, '2025-07-27 13:13:41'),
(52, 10, 5, '2025-07-27 13:13:41'),
(53, 10, 4, '2025-07-27 13:13:41'),
(54, 10, 4, '2025-07-27 13:13:41'),
(55, 11, 4, '2025-07-27 13:13:41'),
(56, 11, 3, '2025-07-27 13:13:41'),
(57, 11, 5, '2025-07-27 13:13:41'),
(58, 12, 5, '2025-07-27 13:13:41'),
(59, 12, 5, '2025-07-27 13:13:41'),
(60, 12, 4, '2025-07-27 13:13:41'),
(61, 12, 4, '2025-07-27 13:13:41'),
(62, 13, 4, '2025-07-27 13:13:41'),
(63, 13, 3, '2025-07-27 13:13:41'),
(64, 13, 5, '2025-07-27 13:13:41'),
(65, 14, 4, '2025-07-27 13:13:41'),
(66, 14, 5, '2025-07-27 13:13:41'),
(67, 14, 4, '2025-07-27 13:13:41'),
(68, 15, 5, '2025-07-27 13:13:41'),
(69, 15, 4, '2025-07-27 13:13:41'),
(70, 15, 5, '2025-07-27 13:13:41'),
(71, 16, 4, '2025-07-27 13:13:41'),
(72, 16, 5, '2025-07-27 13:13:41'),
(73, 16, 3, '2025-07-27 13:13:41'),
(74, 17, 5, '2025-07-27 13:13:41'),
(75, 17, 4, '2025-07-27 13:13:41'),
(76, 17, 4, '2025-07-27 13:13:41'),
(77, 18, 5, '2025-07-27 13:13:41'),
(78, 18, 3, '2025-07-27 13:13:41'),
(79, 18, 4, '2025-07-27 13:13:41'),
(80, 19, 5, '2025-07-27 13:13:41'),
(81, 19, 4, '2025-07-27 13:13:41'),
(82, 19, 5, '2025-07-27 13:13:41'),
(83, 19, 5, '2025-07-27 13:13:41'),
(84, 20, 4, '2025-07-27 13:13:41'),
(85, 20, 5, '2025-07-27 13:13:41'),
(86, 20, 3, '2025-07-27 13:13:41'),
(87, 21, 5, '2025-07-27 13:13:41'),
(88, 21, 4, '2025-07-27 13:13:41'),
(89, 21, 4, '2025-07-27 13:13:41'),
(90, 22, 4, '2025-07-27 13:13:41'),
(91, 22, 5, '2025-07-27 13:13:41'),
(92, 22, 3, '2025-07-27 13:13:41'),
(93, 23, 5, '2025-07-27 13:13:41'),
(94, 23, 4, '2025-07-27 13:13:41'),
(95, 23, 4, '2025-07-27 13:13:41'),
(96, 24, 4, '2025-07-27 13:13:41'),
(97, 24, 5, '2025-07-27 13:13:41'),
(98, 24, 3, '2025-07-27 13:13:41'),
(99, 25, 5, '2025-07-27 13:13:41'),
(100, 25, 4, '2025-07-27 13:13:41'),
(101, 25, 5, '2025-07-27 13:13:41');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_log`
--

CREATE TABLE `transaction_log` (
  `log_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'Cash',
  `payment_status` varchar(50) DEFAULT 'Pending',
  `amount` decimal(10,2) DEFAULT 0.00,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_log`
--

INSERT INTO `transaction_log` (`log_id`, `order_id`, `payment_method`, `payment_status`, `amount`, `timestamp`) VALUES
(1, 11, 'Cash', 'Pending', 650.00, '2025-07-25 04:43:48'),
(2, 12, 'Cash', 'Pending', 550.00, '2025-07-25 04:45:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','staff','customer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(5, 'admin', '$2y$10$PtX32S29ePGWVaBwSOvRAeIgt/9EjLG42hL3Hdy0618lJ21w4kvga', 'admin1@booknest.com', 'admin', '2025-07-17 12:20:48'),
(6, 'staff', '$2y$10$HZnNT2n2orgKhNfqGDmRpOOWRnN/NN5mVpubC284qdoimILtVbrvi', 'staff1@booknest.com', 'staff', '2025-07-17 12:20:48'),
(7, 'cust1', '$2y$10$ZF/2hdkkSikN9lBMd.CBEu6f4ijDaX3d1RVzPKH7VqjPUbfY8YKKi', 'cust1@email.com', 'customer', '2025-07-17 12:20:48'),
(8, 'cust2', '$2y$10$ZF/2hdkkSikN9lBMd.CBEu6f4ijDaX3d1RVzPKH7VqjPUbfY8YKKi', 'cust2@email.com', 'customer', '2025-07-17 12:20:48');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `trg_log_signup` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO logs(action, description, created_at)
    VALUES('SIGNUP', CONCAT('New user ', NEW.username, ' registered.'), NOW());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_currency_preference`
--

CREATE TABLE `user_currency_preference` (
  `user_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_currency_preference`
--

INSERT INTO `user_currency_preference` (`user_id`, `currency_id`) VALUES
(7, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`currency_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `transaction_log`
--
ALTER TABLE `transaction_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_currency_preference`
--
ALTER TABLE `user_currency_preference`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `currency_id` (`currency_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `currency_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `transaction_log`
--
ALTER TABLE `transaction_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE;

--
-- Constraints for table `transaction_log`
--
ALTER TABLE `transaction_log`
  ADD CONSTRAINT `transaction_log_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `user_currency_preference`
--
ALTER TABLE `user_currency_preference`
  ADD CONSTRAINT `user_currency_preference_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `user_currency_preference_ibfk_2` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`currency_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
