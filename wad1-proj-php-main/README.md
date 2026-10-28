# La Verdad Found— SQLite + PDO

A web-based lost and found management system for La Verdad Christian College, built with PHP, SQLite, PDO, and Bootstrap. This application follows secure coding standards including prepared statements, session management, CSRF protection, and standardized CRUD patterns.

## Features

* **Surrender Found Items (`create.php`):** Allows students, faculty/staff, and guests to submit surrender tickets for items found on campus.
* **Dashboard (`index.php`):** Displays all surrender tickets sorted newest first with category mappings and status badges.
* **Edit Ticket (`edit.php`):** Updates item information, status (`Pending Verification`, `Verified`, `Stored`, `Rejected`), and category assignments.
* **Delete Ticket (`delete.php`):** Securely removes surrender records with confirmation prompts.
* **Database Setup (`setup.php`):** Initializes relational tables (`users`, `categories`, `surrender_tickets`, `stored_items`, `claim_requests`) and seeds initial category and security guard records.

## Requirements

Requires PHP 8.1+ with PDO SQLite and `mbstring` enabled.

## Run

1. Run `php setup.php` once in your terminal to create the database tables and sample records.
2. Run `php -S 127.0.0.1:8000` to start the PHP development server.
3. Open `<http://127.0.0.1:8000/index.php>` in your web browser.

Alternatively, start the server and visit `/setup.php` before `/index.php`. Setup preserves existing records. If the tables are empty, it automatically populates initial categories (Electronics, IDs & Cards, Keys, etc.) and a default security guard user.

## Project Structure

```text
la_verdad_found.db    SQLite database file
setup.php             Creates relational tables and inserts initial seed data
database.php          Opens the shared PDO connection
index.php             Reads and lists surrender tickets newest first
create.php            Validates the form and inserts a surrender ticket
edit.php              Loads, validates, and updates a surrender ticket
delete.php            Confirms and deletes a surrender ticket
styles.css            Sets form max-width; Bootstrap handles the UI