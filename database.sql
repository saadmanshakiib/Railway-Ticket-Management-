CREATE DATABASE IF NOT EXISTS train_db;
USE train_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE trains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    train_number VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    source VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    total_seats INT NOT NULL
);

CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    train_id INT NOT NULL,
    travel_date DATE NOT NULL,
    departure_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    available_seats INT NOT NULL,
    FOREIGN KEY (train_id) REFERENCES trains(id) ON DELETE CASCADE
);

CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    schedule_id INT NOT NULL,
    seats INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Booked',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password, role) VALUES
('System Admin', 'admin@example.com', 'admin123', 'admin');
