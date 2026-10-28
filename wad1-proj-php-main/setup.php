<?php
// Run this file once before opening index.php.
require __DIR__ . '/database.php';

try {
    $pdo->beginTransaction();

    // 1. Users / Security Guards Table
    $sql = 'CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                role TEXT NOT NULL CHECK(role IN ("SecurityGuard", "Admin")) DEFAULT "SecurityGuard",
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )';
    $pdo->exec($sql);

    // 2. Categories Table
    $sql = 'CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_name TEXT NOT NULL UNIQUE
            )';
    $pdo->exec($sql);

    // 3. Surrender Tickets Table
    $sql = 'CREATE TABLE IF NOT EXISTS surrender_tickets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reporter_type TEXT NOT NULL CHECK(reporter_type IN ("Guest", "Student", "FacultyStaff")),
                reporter_name TEXT NOT NULL,
                reporter_email TEXT NULL,
                item_name TEXT NOT NULL,
                category_id INTEGER NOT NULL,
                item_type TEXT NOT NULL CHECK(item_type IN ("Lost", "Found")) DEFAULT "Found",
                location_found TEXT NOT NULL,
                time_found TEXT NOT NULL,
                other_info TEXT NULL,
                photo_path TEXT NULL,
                status TEXT DEFAULT "Pending Verification" CHECK(status IN ("Pending Verification", "Verified", "Stored", "Rejected")),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            )';
    $pdo->exec($sql);

    // 4. Stored Items Table
    $sql = 'CREATE TABLE IF NOT EXISTS stored_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                surrender_ticket_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                description TEXT NULL,
                category_id INTEGER NOT NULL,
                location TEXT NOT NULL,
                status TEXT DEFAULT "Available" CHECK(status IN ("Available", "Pending Claim", "Released")),
                FOREIGN KEY (surrender_ticket_id) REFERENCES surrender_tickets(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            )';
    $pdo->exec($sql);

    // 5. Claim Requests Table
    $sql = 'CREATE TABLE IF NOT EXISTS claim_requests (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                stored_item_id INTEGER NOT NULL,
                claimant_name TEXT NOT NULL,
                claimant_id_number TEXT NOT NULL,
                lv_email TEXT NOT NULL,
                evidence_details TEXT NOT NULL,
                proof_photo_path TEXT NULL,
                claim_status TEXT DEFAULT "Pending Review" CHECK(claim_status IN ("Pending Review", "Approved - Ready for Release", "Completed")),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (stored_item_id) REFERENCES stored_items(id) ON DELETE CASCADE
            )';
    $pdo->exec($sql);

    // Seed initial data
    $categoryStatement = $pdo->query('SELECT COUNT(*) FROM categories');
    $categoryCount = (int) $categoryStatement->fetchColumn();
    $userStatement = $pdo->query('SELECT COUNT(*) FROM users');
    $userCount = (int) $userStatement->fetchColumn();

    if ($categoryCount === 0) {
        $categories = ['Electronics', 'IDs & Cards', 'Keys', 'Clothing & Accessories', 'Books & Notebooks', 'Bags & Wallets', 'Others'];
        $sql = 'INSERT INTO categories (category_name) VALUES (:category_name)';
        $statement = $pdo->prepare($sql);
        foreach ($categories as $catName) {
            $statement->execute(['category_name' => $catName]);
        }
    }

    if ($userCount === 0) {
        $sql = 'INSERT INTO users (username, password, role) VALUES (:username, :password, :role)';
        $statement = $pdo->prepare($sql);
        $statement->execute([
            'username' => 'security_guard',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role' => 'SecurityGuard'
        ]);
    }

    $pdo->commit();
    echo 'Database setup complete. Existing records have been preserved.';
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($exception->getMessage());
    http_response_code(500);
    exit('Database setup failed. Check the PHP error log for details.');
}
?>