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

$ticketId = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($ticketId === false) {
    http_response_code(404);
    exit('Ticket not found.');
}

$sql = 'SELECT surrender_tickets.*, categories.category_name
        FROM surrender_tickets
        JOIN categories ON categories.id = surrender_tickets.category_id
        WHERE surrender_tickets.id = :id';
$statement = $pdo->prepare($sql);
$statement->execute(['id' => $ticketId]);
$ticket = $statement->fetch();

if ($ticket === false) {
    http_response_code(404);
    exit('Ticket not found.');
}

$categoryStatement = $pdo->query('SELECT * FROM categories ORDER BY category_name');
$categories = $categoryStatement->fetchAll();
$ticketData = $ticket;
$validationErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !is_string($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        http_response_code(403);
        exit('Invalid form token.');
    }

    $ticketData = [
        'reporter_type' => trim($_POST['reporter_type'] ?? ''),
        'reporter_name' => trim($_POST['reporter_name'] ?? ''),
        'reporter_email' => trim($_POST['reporter_email'] ?? ''),
        'item_name' => trim($_POST['item_name'] ?? ''),
        'category_id' => trim($_POST['category_id'] ?? ''),
        'location_found' => trim($_POST['location_found'] ?? ''),
        'time_found' => trim($_POST['time_found'] ?? ''),
        'status' => trim($_POST['status'] ?? 'Pending Verification')
    ];

    if (count($validationErrors) === 0) {
        $sql = 'UPDATE surrender_tickets
                SET reporter_type = :reporter_type, 
                    reporter_name = :reporter_name, 
                    reporter_email = :reporter_email, 
                    item_name = :item_name, 
                    category_id = :category_id, 
                    location_found = :location_found, 
                    time_found = :time_found, 
                    status = :status
                WHERE id = :id';
        $statement = $pdo->prepare($sql);
        $updateParameters = $ticketData;
        $updateParameters['id'] = $ticket['id'];
        $statement->execute($updateParameters);

        $_SESSION['message'] = 'Ticket updated successfully.';
        header('Location: index.php', true, 303);
        exit;
    }
}
$title = 'Edit Ticket';
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
        <a class="d-inline-block mb-3" href="index.php">← Back to dashboard</a>
        <h1 class="h2">Edit Ticket</h1>
        <form method="post" class="ticket-form">
            <input type="hidden" name="csrf" value="<?php echo escapeHtml($_SESSION['csrf']); ?>">
            <div class="mb-3">
                <label class="form-label" for="reporter_name">Reporter Name</label>
                <input class="form-control" id="reporter_name" name="reporter_name" required
                    value="<?php echo escapeHtml($ticketData['reporter_name']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="item_name">Item Name</label>
                <input class="form-control" id="item_name" name="item_name" required
                    value="<?php echo escapeHtml($ticketData['item_name']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="category_id">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <?php foreach ($categories as $cat) { ?>
                        <option value="<?php echo escapeHtml($cat['id']); ?>" <?php echo (string) $ticketData['category_id'] === (string) $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo escapeHtml($cat['category_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="Pending Verification" <?php echo $ticketData['status'] === 'Pending Verification' ? 'selected' : ''; ?>>Pending Verification</option>
                    <option value="Verified" <?php echo $ticketData['status'] === 'Verified' ? 'selected' : ''; ?>>
                        Verified</option>
                    <option value="Stored" <?php echo $ticketData['status'] === 'Stored' ? 'selected' : ''; ?>>Stored
                    </option>
                    <option value="Rejected" <?php echo $ticketData['status'] === 'Rejected' ? 'selected' : ''; ?>>
                        Rejected</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="location_found">Location Found</label>
                <input class="form-control" id="location_found" name="location_found" required
                    value="<?php echo escapeHtml($ticketData['location_found']); ?>">
            </div>
            <input type="hidden" name="reporter_type" value="<?php echo escapeHtml($ticketData['reporter_type']); ?>">
            <input type="hidden" name="reporter_email" value="<?php echo escapeHtml($ticketData['reporter_email']); ?>">
            <input type="hidden" name="time_found" value="<?php echo escapeHtml($ticketData['time_found']); ?>">
            <div class="d-flex gap-2 mt-3">
                <a class="btn btn-secondary" href="index.php">Cancel</a>
                <button class="btn btn-primary" type="submit">Save changes</button>
            </div>
        </form>
    </main>
</body>

</html>