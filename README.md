# Train Ticket System

## Setup

1. Start Apache and MySQL from XAMPP.
2. Open phpMyAdmin at `http://localhost/phpmyadmin`.
3. Import `database.sql`. It creates the `train_db` database and all tables.
4. Open `http://localhost/TicketRailway/app/`.
5. Log in as `admin@example.com` with password `admin123`.

The admin can add managers, customers, trains, schedules, and view/delete tickets.
Managers can manage customers, trains, and schedules.
Customers can register, log in, search trains by source, destination and date, and book available seats.

//If this folder is renamed, change `BASE_URL` in `app/includes/auth.php` to the new URL path.
