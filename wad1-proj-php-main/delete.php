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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !is_string($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        http_response_code(403);
        exit('Invalid form token.');
    }

    $sql = 'DELETE FROM surrender_tickets WHERE id = :id';
    $statement = $pdo->prepare($sql);
    $statement->execute(['id' => $ticket['id']]);

    $_SESSION['message'] = 'Ticket deleted successfully.';
    header('Location: index.php', true, 303);
    exit;
}
$title = 'Delete Ticket';
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
        <h1 class="h2">Delete surrender ticket?</h1>
        <form method="post" class="ticket-form border rounded p-4">
            <input type="hidden" name="csrf" value="<?php echo escapeHtml($_SESSION['csrf']); ?>">
            <h2 class="h5">Item: <?php echo escapeHtml($ticket['item_name']); ?></h2>
            <p>Reporter: <?php echo escapeHtml($ticket['reporter_name']); ?></p>
            <div class="d-flex gap-2 mt-4">
                <a class="btn btn-secondary" href="index.php">Keep ticket</a>
                <button class="btn btn-danger" type="submit">Delete ticket</button>
            </div>
        </form>
    </main>
</body>

</html>