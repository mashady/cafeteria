<?php
include '../../includes/header.php';
include '../../db/connect.php';
include '../../includes/admin_auth.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['confirm_cancel'])) {
    $order_id = $_POST['order_id'];
    $update_sql = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);

    header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['user_id']) ? "?user_id=" . $_GET['user_id'] : ""));
    exit();
}

if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
    mysqli_stmt_execute($stmt);

    header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['user_id']) ? "?user_id=" . $_GET['user_id'] : ""));
    exit();
}

$users = [];
$user_result = mysqli_query($conn, "SELECT id, name FROM users");
while ($row = mysqli_fetch_assoc($user_result)) {
    $users[] = $row;
}

$items_per_page = 10; 
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); 
$offset = ($current_page - 1) * $items_per_page;

$orders = [];
$selected_user_id = $_GET['user_id'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

if ($start_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
    $start_date = null;
}
if ($end_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
    $end_date = null;
}

$end_date_query = $end_date ? date('Y-m-d', strtotime($end_date . ' +1 day')) : null;

$count_query = "
    SELECT COUNT(DISTINCT o.id) as total_orders
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE 1=1
";

$count_params = [];
$count_types = '';

if ($selected_user_id) {
    $count_query .= " AND o.user_id = ?";
    $count_params[] = $selected_user_id;
    $count_types .= 'i';
}

if ($start_date) {
    $count_query .= " AND o.created_at >= ?";
    $count_params[] = $start_date;
    $count_types .= 's';
}

if ($end_date) {
    $count_query .= " AND o.created_at < ?";
    $count_params[] = $end_date_query;
    $count_types .= 's';
}

$count_stmt = mysqli_prepare($conn, $count_query);

if ($count_params) {
    mysqli_stmt_bind_param($count_stmt, $count_types, ...$count_params);
}

mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_row = mysqli_fetch_assoc($count_result);
$total_orders = $count_row['total_orders'];

$total_pages = ceil($total_orders / $items_per_page);
$current_page = min($current_page, max(1, $total_pages)); 

$query = "
    SELECT o.id AS order_id, o.total, o.notes, o.status, o.created_at, 
           p.name AS product_name, op.quantity, u.name AS user_name
    FROM orders o
    JOIN order_products op ON o.id = op.order_id
    JOIN products p ON op.product_id = p.id
    JOIN users u ON o.user_id = u.id
    WHERE 1=1
";

$params = [];
$types = '';

if ($selected_user_id) {
    $query .= " AND o.user_id = ?";
    $params[] = $selected_user_id;
    $types .= 'i';
}

if ($start_date) {
    $query .= " AND o.created_at >= ?";
    $params[] = $start_date;
    $types .= 's';
}

if ($end_date) {
    $query .= " AND o.created_at < ?";
    $params[] = $end_date_query;
    $types .= 's';
}

$query .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$params[] = $items_per_page;
$params[] = $offset;
$types .= 'ii';

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
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="user_id" class="form-label">Select User</label>
                <select name="user_id" id="user_id" class="form-select">
                    <option value="">All Users</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= ($selected_user_id == $user['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <?php if ($orders): ?>
        <?php foreach ($orders as $order_id => $order): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #<?= $order_id ?></strong> |
                        
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="order_id" value="<?= $order_id ?>">
                            <select name="status" onchange="this.form.submit()" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="out for delivery" <?= $order['status'] === 'out for delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                <option value="done" <?= $order['status'] === 'done' ? 'selected' : '' ?>>Done</option>
                                <?php if ( $order['status'] === 'cancelled'): ?>
                                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                <?php endif; ?>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>

                        | Date: <?= date("Y-m-d H:i", strtotime($order['created_at'])) ?>

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
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Order pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                        <?php
                        $prev_page_params = $_GET;
                        $prev_page_params['page'] = $current_page - 1;
                        if ($prev_page_params['page'] < 1) unset($prev_page_params['page']);
                        ?>
                        <a class="page-link" href="?<?= http_build_query($prev_page_params) ?>" aria-label="Previous">
                            <span aria-hidden="true">Prev</span>
                        </a>
                    </li>
                    
                    <?php
                    $range = 2; 
                    $start_page = max(1, $current_page - $range);
                    $end_page = min($total_pages, $current_page + $range);
                    
                    if ($start_page > 1) {
                        $first_page_params = $_GET;
                        $first_page_params['page'] = 1;
                        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($first_page_params) . '">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $page_params = $_GET;
                        $page_params['page'] = $i;
                        if ($i == 1) unset($page_params['page']); 
                        
                        echo '<li class="page-item ' . (($i == $current_page) ? 'active' : '') . '">
                                <a class="page-link" href="?' . http_build_query($page_params) . '">' . $i . '</a>
                              </li>';
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                        }
                        $last_page_params = $_GET;
                        $last_page_params['page'] = $total_pages;
                        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($last_page_params) . '">' . $total_pages . '</a></li>';
                    }
                    ?>
                    
                    <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                        <?php
                        $next_page_params = $_GET;
                        $next_page_params['page'] = $current_page + 1;
                        ?>
                        <a class="page-link" href="?<?= http_build_query($next_page_params) ?>" aria-label="Next">
                            <span aria-hidden="true">Next</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-warning">No orders found.</div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>