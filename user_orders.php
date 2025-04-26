<?php
session_start();
include './includes/header.php';
include './db/connect.php';
/*
if (!isset($_SESSION['user_id'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>You must be logged in to view your orders.</div></div>";
    include './includes/footer.php';
    exit;
}*/

$user_id = $_SESSION['user_id'] ?? 1;
$orders = [];

$query = "
    SELECT o.id AS order_id, o.total, o.notes, o.status, o.created_at, 
           p.name AS product_name, op.quantity
    FROM orders o
    JOIN order_products op ON o.id = op.order_id
    JOIN products p ON op.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $orders[$row['order_id']]['details'][] = $row;
    $orders[$row['order_id']]['total'] = $row['total'];
    $orders[$row['order_id']]['notes'] = $row['notes'];
    $orders[$row['order_id']]['status'] = $row['status'];
    $orders[$row['order_id']]['created_at'] = $row['created_at'];
}
?>

<div class="container mt-5">
    <h2 class="mb-4">My Orders</h2>

    <?php if ($orders): ?>
        <?php foreach ($orders as $order_id => $order): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Order #<?= $order_id ?></strong> | Status: <?= ucfirst($order['status']) ?> |
                    Date: <?= date("Y-m-d H:i", strtotime($order['created_at'])) ?>
                </div>
                <div class="card-body">
                    <p><strong>Notes:</strong> <?= $order['notes'] ?: 'None' ?></p>
                    <ul class="list-group">
                        <?php foreach ($order['details'] as $item): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <?= htmlspecialchars($item['product_name']) ?> 
                                <span>Qty: <?= $item['quantity'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="mt-2"><strong>Total:</strong> <?= $order['total'] ?> EGP</p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">You haven’t placed any orders yet.</div>
    <?php endif; ?>
</div>

<?php include './includes/footer.php'; ?>
