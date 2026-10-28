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

$sql = 'SELECT surrender_tickets.*, categories.category_name
        FROM surrender_tickets
        JOIN categories ON surrender_tickets.category_id = categories.id
        ORDER BY surrender_tickets.id DESC';
$ticketStatement = $pdo->query($sql);
$tickets = $ticketStatement->fetchAll();
$title = 'Surrender Tickets Dashboard';
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
    <nav class="navbar navbar-dark bg-dark mb-4" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand" href="index.php">La Verdad Found</a>
        </div>
    </nav>
    <main class="container pb-5">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="h2 mb-0">Surrender Tickets</h1>
            <a class="btn btn-primary" href="create.php">Surrender Found Item</a>
        </div>

        <?php if (isset($_SESSION['message'])) { ?>
            <div class="alert alert-success" role="status">
                <?php echo escapeHtml($_SESSION['message']); ?>
            </div>
            <?php
            unset($_SESSION['message']);
        }
        ?>

        <?php if (count($tickets) === 0) { ?>
            <div class="border rounded p-4 text-center">
                <h2>No surrender tickets yet</h2>
                <p>Submit your first found item report to populate the directory.</p>
                <a class="btn btn-primary" href="create.php">Surrender Found Item</a>
            </div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Item Name</th>
                            <th scope="col">Category</th>
                            <th scope="col">Reporter</th>
                            <th scope="col">Location Found</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket) { ?>
                            <tr>
                                <td class="text-secondary">#<?php echo escapeHtml($ticket['id']); ?></td>
                                <td class="fw-medium"><?php echo escapeHtml($ticket['item_name']); ?></td>
                                <td><span><?php echo escapeHtml($ticket['category_name']); ?></span></td>
                                <td>
                                    <?php echo escapeHtml($ticket['reporter_name']); ?>
                                    <span class="text-muted small">(<?php echo escapeHtml($ticket['reporter_type']); ?>)</span>
                                </td>
                                <td><?php echo escapeHtml($ticket['location_found']); ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo escapeHtml($ticket['status']); ?></span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-outline-primary me-1"
                                        href="edit.php?id=<?php echo escapeHtml($ticket['id']); ?>">Edit</a>
                                    <a class="btn btn-sm btn-outline-danger"
                                        href="delete.php?id=<?php echo escapeHtml($ticket['id']); ?>">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

    </main>
</body>

</html>