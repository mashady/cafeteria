<?php
include '../../includes/header.php';
include '../../db/connect.php';
include '../../includes/admin_auth.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
}

if (isset($_POST['confirm_cancel'])) {
    $order_id = $_POST['order_id'];
    $update_sql = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    
    header("Location: ".$_SERVER['PHP_SELF'].($selected_user_id ? "?user_id=".$selected_user_id : ""));
    exit();
}

$users = [];
$user_result = mysqli_query($conn, "SELECT id, name FROM users");
while ($row = mysqli_fetch_assoc($user_result)) {
    $users[] = $row;
}

$orders = [];
$selected_user_id = $_GET['user_id'] ?? null;

$query = "
    SELECT o.id AS order_id, o.total, o.notes, o.status, o.created_at, 
           p.name AS product_name, op.quantity, u.name AS user_name
    FROM orders o
    JOIN order_products op ON o.id = op.order_id
    JOIN products p ON op.product_id = p.id
    JOIN users u ON o.user_id = u.id
";

$params = [];
$types = '';

if ($selected_user_id) {
    $query .= " WHERE o.user_id = ?";
    $params[] = $selected_user_id;
    $types .= 'i';
}

$query .= " ORDER BY o.created_at DESC";

$stmt = mysqli_prepare($conn, $query);

if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $orders[$row['order_id']]['details'][] = $row;
    $orders[$row['order_id']]['total'] = $row['total'];
    $orders[$row['order_id']]['notes'] = $row['notes'];
    $orders[$row['order_id']]['status'] = $row['status'];
    $orders[$row['order_id']]['created_at'] = $row['created_at'];
    $orders[$row['order_id']]['user_name'] = $row['user_name'];
}
?>

<div class="container mt-5">
    <h2 class="mb-4">User Orders</h2>

    <form method="GET" class="mb-4">
        <label for="user_id" class="form-label">Select User</label>
        <select name="user_id" id="user_id" class="form-select w-50" onchange="this.form.submit()">
            <option value="">All Users</option>
            <?php foreach ($users as $user): ?>
                <option value="<?= $user['id'] ?>" <?= ($selected_user_id == $user['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($user['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($orders): ?>
        <?php foreach ($orders as $order_id => $order): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #<?= $order_id ?></strong> | 
                        Status: <?= ucfirst($order['status']) ?> |
                        Date: <?= date("Y-m-d H:i", strtotime($order['created_at'])) ?>
                        <?php if (!$selected_user_id): ?>
                            | <span class="text-muted">User: <?= htmlspecialchars($order['user_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($order['status'] == 'processing'): ?>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                data-bs-toggle="modal" data-bs-target="#cancelModal<?= $order_id ?>">
                            Cancel Order
                        </button>
                    <?php endif; ?>
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

            <?php if ($order['status'] == 'processing'): ?>
            <div class="modal fade" id="cancelModal<?= $order_id ?>" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelModalLabel">Confirm Order Cancellation</h5>
                            <button type="button" class="btn-close" data-bs-close="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to cancel Order #<?= $order_id ?>?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                                <button type="submit" name="confirm_cancel" class="btn btn-danger">Confirm Cancellation</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning">No orders found.</div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>