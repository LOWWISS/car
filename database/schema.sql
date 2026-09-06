-- ============================================================
--  Car Auction System — Database Schema
--  Engine: InnoDB | Charset: utf8mb4
--  All FKs with ON DELETE CASCADE where appropriate.
-- ============================================================
CREATE DATABASE IF NOT EXISTS car_auction CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE car_auction;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------- users ----------
CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    email           VARCHAR(190) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('buyer','seller','admin') NOT NULL DEFAULT 'buyer',
    phone           VARBINARY(255) NULL,           -- AES-256 encrypted at rest
    email_verified  TINYINT(1) NOT NULL DEFAULT 0,
    verify_token    VARCHAR(64) NULL,
    reset_token     VARCHAR(64) NULL,
    reset_expires   DATETIME NULL,
    failed_attempts INT NOT NULL DEFAULT 0,
    locked_until    DATETIME NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- cars ----------
CREATE TABLE cars (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id       INT UNSIGNED NOT NULL,
    title           VARCHAR(150) NOT NULL,
    make            VARCHAR(60) NOT NULL,
    model           VARCHAR(60) NOT NULL,
    year            SMALLINT NOT NULL,
    mileage         INT UNSIGNED NOT NULL DEFAULT 0,
    body_type       VARCHAR(40) NOT NULL DEFAULT 'Sedan',
    `condition`     VARCHAR(40) NOT NULL DEFAULT 'Used',
    location        VARCHAR(100) NOT NULL DEFAULT '',
    description     TEXT NULL,
    starting_price  DECIMAL(12,2) NOT NULL,
    reserve_price   DECIMAL(12,2) NULL,
    buy_now_price   DECIMAL(12,2) NULL,
    auction_start   DATETIME NOT NULL,
    auction_end     DATETIME NOT NULL,
    status          ENUM('pending','active','sold','closed','rejected') NOT NULL DEFAULT 'pending',
    approved_by     INT UNSIGNED NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status_end (status, auction_end),
    INDEX idx_make (make),
    INDEX idx_seller (seller_id),
    CONSTRAINT fk_cars_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- car_images ----------
CREATE TABLE car_images (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    car_id      INT UNSIGNED NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    is_primary  TINYINT(1) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_img_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- bids ----------
CREATE TABLE bids (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    car_id      INT UNSIGNED NOT NULL,
    bidder_id   INT UNSIGNED NOT NULL,
    bid_amount  DECIMAL(12,2) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_car_amount (car_id, bid_amount DESC),
    INDEX idx_bidder (bidder_id),
    CONSTRAINT fk_bid_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    CONSTRAINT fk_bid_bidder FOREIGN KEY (bidder_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- watchlist ----------
CREATE TABLE watchlist (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    car_id     INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_watch (user_id, car_id),
    CONSTRAINT fk_watch_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_watch_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- notifications ----------
CREATE TABLE notifications (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    message    VARCHAR(255) NOT NULL,
    type       VARCHAR(40) NOT NULL DEFAULT 'info',
    link       VARCHAR(255) NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_read (user_id, is_read),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- rate_limits ----------
CREATE TABLE rate_limits (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action         VARCHAR(40) NOT NULL,
    ip_address     VARCHAR(45) NOT NULL,
    user_id        INT UNSIGNED NULL,
    attempt_count  INT NOT NULL DEFAULT 0,
    window_start   INT NOT NULL DEFAULT 0,
    UNIQUE KEY uniq_rate (action, ip_address, user_id)
) ENGINE=InnoDB;

-- ---------- audit_logs ----------
CREATE TABLE audit_logs (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NULL,
    action     VARCHAR(60) NOT NULL,
    details    TEXT NULL,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action)
) ENGINE=InnoDB;

-- ---------- transactions ----------
-- One transaction per car (UNIQUE car_id) — created when an auction is won
-- or a Buy Now occurs. Tracks the post-auction payment/handover/completion
-- lifecycle. The car's `status` column remains the source of truth for
-- listing state (sold/closed); this table tracks the deal lifecycle.
CREATE TABLE transactions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    car_id          INT UNSIGNED NOT NULL,
    seller_id       INT UNSIGNED NOT NULL,
    winner_id       INT UNSIGNED NOT NULL,
    winning_bid_id  INT UNSIGNED NULL,
    final_amount    DECIMAL(12,2) NOT NULL,
    status          ENUM('payment_pending','payment_confirmed','ready_for_handover','completed','cancelled')
                    NOT NULL DEFAULT 'payment_pending',
    paid_at         DATETIME NULL,
    handed_over_at  DATETIME NULL,
    completed_at    DATETIME NULL,
    cancelled_at    DATETIME NULL,
    notes           VARCHAR(500) NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_tx_car (car_id),
    INDEX idx_tx_seller (seller_id),
    INDEX idx_tx_winner (winner_id),
    INDEX idx_tx_status (status),
    CONSTRAINT fk_tx_car    FOREIGN KEY (car_id)         REFERENCES cars(id) ON DELETE CASCADE,
    CONSTRAINT fk_tx_seller FOREIGN KEY (seller_id)      REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_tx_winner FOREIGN KEY (winner_id)      REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_tx_bid    FOREIGN KEY (winning_bid_id) REFERENCES bids(id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  Seed data (passwords are bcrypt hashes of 'password')
-- ============================================================
-- Password for all seeded users: "password" (bcrypt cost 12)
INSERT INTO users (name, email, password_hash, role, email_verified, is_active) VALUES
('Admin User',  'admin@car.local', '$2y$12$gwxAU0AdAZDagN21SuCZteHShbR6IaTeoKR3Nmiu1Tw8uXfGWk5rS', 'admin',  1, 1),
('Jane Seller', 'jane@car.local',  '$2y$12$gwxAU0AdAZDagN21SuCZteHShbR6IaTeoKR3Nmiu1Tw8uXfGWk5rS', 'seller', 1, 1),
('Bob Bidder',  'bob@car.local',   '$2y$12$gwxAU0AdAZDagN21SuCZteHShbR6IaTeoKR3Nmiu1Tw8uXfGWk5rS', 'buyer',  1, 1);

-- seller_id = 2 (Jane). Create a few listings spanning active/ending-soon/pending.
INSERT INTO cars (seller_id, title, make, model, year, mileage, body_type, `condition`, location, description, starting_price, reserve_price, buy_now_price, auction_start, auction_end, status) VALUES
(2, '2021 Toyota Corolla SE', 'Toyota', 'Corolla', 2021, 24000, 'Sedan', 'Used', 'Manila, PH', 'Well maintained, single owner, full service history.', 500000.00, 600000.00, 750000.00, NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY), 'active'),
(2, '2019 Honda Civic RS Turbo', 'Honda', 'Civic', 2019, 45000, 'Sedan', 'Used', 'Cebu, PH', 'Sporty and reliable. Turbocharged 1.5L engine.', 700000.00, 800000.00, 950000.00, NOW(), DATE_ADD(NOW(), INTERVAL 2 HOUR), 'active'),
(2, '2022 Ford Ranger XLT', 'Ford', 'Ranger', 2022, 18000, 'Pickup', 'Used', 'Davao, PH', 'Tough pickup, ready for work or weekend.', 1200000.00, 1350000.00, 1500000.00, NOW(), DATE_ADD(NOW(), INTERVAL 5 DAY), 'active'),
(2, '2018 Mazda 3 Hatchback', 'Mazda', '3', 2018, 60000, 'Hatchback', 'Used', 'Manila, PH', 'Sleek hatchback with premium interior.', 550000.00, 620000.00, NULL, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'pending');

INSERT INTO car_images (car_id, image_path, is_primary) VALUES
(1, 'uploads/sample1.jpg', 1),
(2, 'uploads/sample2.jpg', 1),
(3, 'uploads/sample3.jpg', 1),
(4, 'uploads/sample4.jpg', 1);

INSERT INTO bids (car_id, bidder_id, bid_amount) VALUES
(1, 3, 510000.00),
(1, 3, 525000.00),
(2, 3, 710000.00);

INSERT INTO notifications (user_id, message, type, link) VALUES
(3, 'You are the highest bidder on 2021 Toyota Corolla SE.', 'bid', '/cars/view/1'),
(2, 'A new bid was placed on your listing.', 'bid', '/cars/view/1');
