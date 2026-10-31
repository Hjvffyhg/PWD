-- BAPS e-Library database (import this in phpMyAdmin)
CREATE DATABASE IF NOT EXISTS baps_elibrary CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE baps_elibrary;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS ratings;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;
-- extra safety: remove any old/leftover tables from earlier versions
DROP TABLE IF EXISTS borrow_records;
DROP TABLE IF EXISTS borrowings;
DROP TABLE IF EXISTS categories;

CREATE TABLE users (
    user_id      INT AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(100) NOT NULL,
    student_no   VARCHAR(20)  DEFAULT NULL UNIQUE,
    email        VARCHAR(100) DEFAULT NULL UNIQUE,
    course_year  VARCHAR(50)  DEFAULT NULL,
    username     VARCHAR(50)  NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    role         ENUM('student','librarian') NOT NULL DEFAULT 'student',
    profile_pic  VARCHAR(255) DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE books (
    book_id          INT AUTO_INCREMENT PRIMARY KEY,
    title            VARCHAR(200) NOT NULL,
    author           VARCHAR(120) NOT NULL,
    isbn             VARCHAR(20)  NOT NULL UNIQUE,
    category         VARCHAR(60)  NOT NULL,
    abstract         TEXT,
    pages            INT DEFAULT 0,
    total_copies     INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    cover            VARCHAR(255) DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- status: pending (waiting for librarian) -> borrowed -> return_requested -> returned | rejected
CREATE TABLE transactions (
    trans_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    book_id      INT NOT NULL,
    status       ENUM('pending','borrowed','return_requested','returned','rejected') NOT NULL DEFAULT 'pending',
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    borrowed_at  DATETIME DEFAULT NULL,
    due_date     DATE DEFAULT NULL,
    returned_at  DATETIME DEFAULT NULL,
    days_late    INT NOT NULL DEFAULT 0,
    penalty_amount INT NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id   INT NOT NULL,
    book_id   INT NOT NULL,
    rating    TINYINT NOT NULL,
    UNIQUE KEY one_rating (user_id, book_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE favorites (
    fav_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    UNIQUE KEY one_fav (user_id, book_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
    notif_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    message    VARCHAR(255) NOT NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- The 4 default librarian accounts are created automatically by PHP
-- (see seed_librarians() in includes/functions.php) the first time any page loads.

INSERT INTO books (title, author, isbn, category, abstract, pages, total_copies, available_copies, cover) VALUES
('Sample Book', 'Sample Author', '978-0-00-000000-0', 'Fiction',
 'This is a sample book so the system is not empty. Librarians can edit or delete it and add real books in the Inventory page.',
 250, 3, 3, 'sample.jpg');

SET FOREIGN_KEY_CHECKS = 1;
