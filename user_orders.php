<?php
session_start();
include './includes/header.php';
include './db/connect.php';

include './includes/auth.php';

/*
if (!isset($_SESSION['user_id'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>You must be logged in to view your orders.</div></div>";
    include './includes/footer.php';
    exit;
}*/
if (isset($_POST['confirm_cancel'])) {
    $order_id = $_POST['order_id'];
    $update_sql = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    
    //header("Location: ".$_SERVER['PHP_SELF'].($selected_user_id ? "?user_id=".$selected_user_id : ""));
    //exit();
}

$user_id = $_SESSION['user']['id'];

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$date_filter_where = '';
$date_filter_params = [];
$date_filter_types = '';

if (!empty($start_date)) {
    $date_filter_where .= " AND o.created_at >= ?";
    $date_filter_params[] = $start_date . ' 00:00:00';
    $date_filter_types .= 's';
}

if (!empty($end_date)) {
    $date_filter_where .= " AND o.created_at <= ?";
    $date_filter_params[] = $end_date . ' 23:59:59';
    $date_filter_types .= 's';
}

$orders_per_page = 2; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
$page = max(1, $page);
$offset = ($page - 1) * $orders_per_page;

$count_query = "SELECT COUNT(DISTINCT o.id) as total FROM orders o WHERE o.user_id = ?" . $date_filter_where;
$count_stmt = mysqli_prepare($conn, $count_query);

$param_count_types = 'i' . $date_filter_types;
$param_count_values = array_merge([$user_id], $date_filter_params);
mysqli_stmt_bind_param($count_stmt, $param_count_types, ...$param_count_values);

mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_orders = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_orders / $orders_per_page);

$orders = [];

$order_ids_query = "
    SELECT o.id 
    FROM orders o
    WHERE o.user_id = ?" . $date_filter_where . "
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
";

$order_ids_stmt = mysqli_prepare($conn, $order_ids_query);

$param_types = 'i' . $date_filter_types . 'ii';
$param_values = array_merge([$user_id], $date_filter_params, [$orders_per_page, $offset]);
mysqli_stmt_bind_param($order_ids_stmt, $param_types, ...$param_values);

mysqli_stmt_execute($order_ids_stmt);
$order_ids_result = mysqli_stmt_get_result($order_ids_stmt);

$order_ids = [];
while ($row = mysqli_fetch_assoc($order_ids_result)) {
    $order_ids[] = $row['id'];
}

if (!empty($order_ids)) {
    $ids_string = implode(',', $order_ids);
    
    $query = "
        SELECT o.id AS order_id, o.total, o.notes, o.status, o.created_at, 
               p.name AS product_name, op.quantity
        FROM orders o
        JOIN order_products op ON o.id = op.order_id
        JOIN products p ON op.product_id = p.id
        WHERE o.id IN ($ids_string)
        ORDER BY o.created_at DESC, o.id
    ";
    
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[$row['order_id']]['details'][] = $row;
        $orders[$row['order_id']]['total'] = $row['total'];
        $orders[$row['order_id']]['notes'] = $row['notes'];
        $orders[$row['order_id']]['status'] = $row['status'];
        $orders[$row['order_id']]['created_at'] = $row['created_at'];
    }
}

function buildPaginationUrl($page_num) {
    $params = $_GET;
    $params['page'] = $page_num;
    return '?' . http_build_query($params);
}
?>

<div class="container mt-5">
    <h2 class="mb-4">My Orders</h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <?php if (!empty($start_date) || !empty($end_date)): ?>
                        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if ($orders): ?>
        <?php foreach ($orders as $order_id => $order): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Order #<?= $order_id ?></strong> | Status: <?= ucfirst($order['status']) ?> |
                    Date: <?= date("Y-m-d H:i", strtotime($order['created_at'])) ?>
                    <?php if ($order['status'] == 'processing'): ?>
                        <button type="button" class="btn btn-sm btn-outline-danger float-end" 
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
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= buildPaginationUrl($page - 1) ?>" aria-label="Previous">
                            <span aria-hidden="true">prev</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link">prev</span>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= buildPaginationUrl($i) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= buildPaginationUrl($page + 1) ?>" aria-label="Next">
                            next
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link">next</span>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="alert alert-info">
            <?php if (!empty($start_date) || !empty($end_date)): ?>
                No orders found in the selected date range.
            <?php else: ?>
                You haven't placed any orders yet.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include './includes/footer.php'; ?>