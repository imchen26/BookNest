DROP DATABASE IF EXISTS booknest;
CREATE DATABASE booknest;
USE booknest;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('Admin', 'Staff', 'Customer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Books
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    category_id INT,
    is_featured BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Orders
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled') DEFAULT 'Pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Order Items
CREATE TABLE order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    book_id INT,
    quantity INT,
    price DECIMAL(10, 2),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (book_id) REFERENCES books(book_id)
);

-- Currency Table
CREATE TABLE currencies (
    currency_id INT AUTO_INCREMENT PRIMARY KEY,
    currency_code VARCHAR(10) NOT NULL,
    exchange_rate DECIMAL(10, 4) NOT NULL  -- relative to PHP
);

-- Cart Table
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT,
    quantity INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (book_id) REFERENCES books(book_id)
);

-- Sample Procedures

DELIMITER $$

CREATE PROCEDURE AddBook(
    IN p_title VARCHAR(255), IN p_author VARCHAR(255), IN p_price DECIMAL(10,2),
    IN p_stock INT, IN p_category_id INT, IN p_is_featured BOOLEAN
)
BEGIN
    INSERT INTO books(title, author, price, stock, category_id, is_featured)
    VALUES(p_title, p_author, p_price, p_stock, p_category_id, p_is_featured);
END$$

CREATE PROCEDURE UpdateBookStock(IN p_book_id INT, IN p_stock INT)
BEGIN
    UPDATE books SET stock = p_stock WHERE book_id = p_book_id;
END$$

CREATE PROCEDURE PlaceOrder(IN p_user_id INT)
BEGIN
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

-- Sample Triggers

CREATE TRIGGER trg_reduce_stock BEFORE INSERT ON order_items
FOR EACH ROW
BEGIN
    IF (SELECT stock FROM books WHERE book_id = NEW.book_id) < NEW.quantity THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Not enough stock';
    END IF;
END$$

CREATE TRIGGER trg_log_signup AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO logs(action, description, created_at)
    VALUES('SIGNUP', CONCAT('New user ', NEW.username, ' registered.'), NOW());
END$$

DELIMITER ;

-- Sample Data
INSERT INTO categories(name) VALUES ('Fiction'), ('Non-Fiction'), ('Science'), ('Children'), ('Business'), ('Mystery');

INSERT INTO currencies(currency_code, exchange_rate) VALUES
('PHP', 1.0000),
('USD', 0.0180),
('KRW', 23.50);

