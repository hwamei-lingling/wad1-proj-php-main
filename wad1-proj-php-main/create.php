<?php
session_start();
require __DIR__ . '/database.php';

function escapeHtml($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$categoryStatement = $pdo->query('SELECT * FROM categories ORDER BY category_name');
$categories = $categoryStatement->fetchAll();

$ticketData = [
    'reporter_type' => 'Student',
    'reporter_name' => '',
    'reporter_email' => '',
    'item_name' => '',
    'category_id' => '',
    'item_type' => 'Found',
    'location_found' => '',
    'time_found' => '',
    'other_info' => ''
];
$validationErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !is_string($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        http_response_code(403);
        exit('Invalid form token. Return and try again.');
    }

    $ticketData = [
        'reporter_type' => trim($_POST['reporter_type'] ?? ''),
        'reporter_name' => trim($_POST['reporter_name'] ?? ''),
        'reporter_email' => trim($_POST['reporter_email'] ?? ''),
        'item_name' => trim($_POST['item_name'] ?? ''),
        'category_id' => trim($_POST['category_id'] ?? ''),
        'item_type' => 'Found',
        'location_found' => trim($_POST['location_found'] ?? ''),
        'time_found' => trim($_POST['time_found'] ?? ''),
        'other_info' => trim($_POST['other_info'] ?? '')
    ];

    if ($ticketData['reporter_name'] === '' || mb_strlen($ticketData['reporter_name']) > 100) {
        $validationErrors['reporter_name'] = 'Enter a reporter name.';
    }
    if ($ticketData['item_name'] === '' || mb_strlen($ticketData['item_name']) > 100) {
        $validationErrors['item_name'] = 'Enter an item name.';
    }
    if ($ticketData['location_found'] === '' || mb_strlen($ticketData['location_found']) > 150) {
        $validationErrors['location_found'] = 'Enter a location.';
    }
    if ($ticketData['time_found'] === '') {
        $validationErrors['time_found'] = 'Specify time found.';
    }

    if (count($validationErrors) === 0) {
        $sql = 'INSERT INTO surrender_tickets (reporter_type, reporter_name, reporter_email, item_name, category_id, item_type, location_found, time_found, other_info, status)
                VALUES (:reporter_type, :reporter_name, :reporter_email, :item_name, :category_id, :item_type, :location_found, :time_found, :other_info, "Pending Verification")';
        $statement = $pdo->prepare($sql);
        $statement->execute([
            'reporter_type' => $ticketData['reporter_type'],
            'reporter_name' => $ticketData['reporter_name'],
            'reporter_email' => $ticketData['reporter_email'] !== '' ? $ticketData['reporter_email'] : null,
            'item_name' => $ticketData['item_name'],
            'category_id' => $ticketData['category_id'],
            'item_type' => $ticketData['item_type'],
            'location_found' => $ticketData['location_found'],
            'time_found' => $ticketData['time_found'],
            'other_info' => $ticketData['other_info'] !== '' ? $ticketData['other_info'] : null
        ]);

        $_SESSION['message'] = 'Surrender ticket submitted successfully.';
        header('Location: index.php', true, 303);
        exit;
    }
    http_response_code(422);
}
$title = 'Surrender Found Item';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo escapeHtml($title); ?> · La Verdad Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">La Verdad Found</a>
        </div>
    </nav>
    <main class="container pb-5">
        <a class="d-inline-block mb-3" href="index.php">← Back to Home</a>
        <h1 class="h2">Surrender Found Item</h1>
        <form method="post" class="ticket-form">
            <input type="hidden" name="csrf" value="<?php echo escapeHtml($_SESSION['csrf']); ?>">
            <div class="mb-3">
                <label class="form-label" for="reporter_type">Reporter Type</label>
                <select class="form-select" id="reporter_type" name="reporter_type">
                    <option value="Student">Student</option>
                    <option value="FacultyStaff">Faculty & Staff</option>
                    <option value="Guest">Guest</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="reporter_name">Your Name</label>
                <input class="form-control" id="reporter_name" name="reporter_name" required
                    value="<?php echo escapeHtml($ticketData['reporter_name']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="reporter_email">Email Address</label>
                <input class="form-control" type="email" id="reporter_email" name="reporter_email"
                    value="<?php echo escapeHtml($ticketData['reporter_email']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="item_name">Item Name</label>
                <input class="form-control" id="item_name" name="item_name" required
                    value="<?php echo escapeHtml($ticketData['item_name']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="category_id">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $cat) { ?>
                        <option value="<?php echo escapeHtml($cat['id']); ?>" <?php echo (string) $ticketData['category_id'] === (string) $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo escapeHtml($cat['category_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="location_found">Location Found</label>
                <input class="form-control" id="location_found" name="location_found" required
                    value="<?php echo escapeHtml($ticketData['location_found']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="time_found">Date & Time Found</label>
                <input class="form-control" type="datetime-local" id="time_found" name="time_found" required
                    value="<?php echo escapeHtml($ticketData['time_found']); ?>">
            </div>
            <div class="d-flex gap-2 mt-3">
                <a class="btn btn-secondary" href="index.php">Cancel</a>
                <button class="btn btn-primary" type="submit">Submit Ticket</button>
            </div>
        </form>
    </main>
</body>

</html>