<?php
include '../../includes/header.php';
include '../../db/connect.php';
include '../../includes/admin_auth.php';

$limit = 5;
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$where = ["o.id IS NOT NULL"]; 

if (!empty($_GET['name'])) {
  $name = mysqli_real_escape_string($conn, $_GET['name']);
  $where[] = "u.name LIKE '%$name%'";
}

if (!empty($_GET['from'])) {
  $from = mysqli_real_escape_string($conn, $_GET['from']);
  $where[] = "o.created_at >= '$from 00:00:00'";
}

if (!empty($_GET['to'])) {
  $to = mysqli_real_escape_string($conn, $_GET['to']);
  $where[] = "o.created_at <= '$to 23:59:59'";
}

$whereSql = '';
if (!empty($where)) {
  $whereSql = "WHERE " . implode(' AND ', $where);
}

$countRes = mysqli_query($conn, "
  SELECT COUNT(DISTINCT u.id) AS total
  FROM users u
  JOIN orders o ON u.id = o.user_id
  $whereSql
");
$countRow = mysqli_fetch_assoc($countRes);
$totalUsers = $countRow['total'];
$totalPages = ceil($totalUsers / $limit);

$sql = "
  SELECT 
    u.id AS user_id,
    u.name,
    IFNULL(SUM(o.total), 0) AS total_orders
  FROM users u
  JOIN orders o ON u.id = o.user_id
  $whereSql
  GROUP BY u.id, u.name
  ORDER BY total_orders DESC
  LIMIT $limit OFFSET $offset
";
$res = mysqli_query($conn, $sql);
?>

<style>
.pagination .page-link { border: none; padding: 0.5rem 0.75rem; transition: background 0.2s, color 0.2s; }
.pagination .page-item.active .page-link { background-color: #0d6efd; color: #fff;  }
.table thead th { background-color: #f8f9fa; color: #495057; }
.table tbody tr:hover { background-color: #f1f1f1; }
</style>

<div class="container w-75 mt-5">
  <h1 class="mb-4">Users Total Orders</h1>

  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <input type="text" name="name" class="form-control" placeholder="Search by user name" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
    </div>
    <div class="col-md-2 d-flex">
      <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
      <?php if (!empty($_GET)): ?>
        <a href="?" class="btn btn-outline-secondary ms-2">Reset</a>
      <?php endif; ?>
    </div>
  </form>

  <div class="card shadow-sm bg-white mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="m-2 fs-5">Users With Orders</h5>
      <a href="../orders/index.php" class="btn btn-primary"><i class="fas fa-arrow"></i> Show all orders</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <?php if (mysqli_num_rows($res) > 0): ?>
          <table class="table table-bordered table-hover mt-3 text-center align-middle bg-white">
            <thead>
              <tr>
                <th>User Name</th>
                <th>Total Orders</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while($user = mysqli_fetch_assoc($res)): ?>
                <tr>
                  <td>
                    <a href="../orders/?user_id=<?= $user['user_id'] ?>" class="text-decoration-none"><?= htmlspecialchars($user['name']) ?></a>
                  </td>
                  <td><?= number_format($user['total_orders'], 2) ?> EGP</td>
                  <td>
                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#orders<?= $user['user_id'] ?>" aria-expanded="false" aria-controls="orders<?= $user['user_id'] ?>">
                      Show Orders
                    </button>
                  </td>
                </tr>

                <tr class="collapse" id="orders<?= $user['user_id'] ?>">
                  <td colspan="3">
                    <div class="position-relative">
                      <button type="button" class="btn-close position-absolute top-0 end-0 m-2" aria-label="Close" onclick="bootstrap.Collapse.getInstance(document.getElementById('orders<?= $user['user_id'] ?>')).hide()"></button>
                      <table class="table table-striped table-hover mt-4">
                        <thead>
                          <tr>
                            <th>Order Date</th>
                            <th>Order Amount</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                            $userId = (int)$user['user_id'];
                            $orderRes = mysqli_query($conn, "
                              SELECT created_at, total
                              FROM orders
                              WHERE user_id = $userId
                              ORDER BY created_at DESC
                            ");
                            if (mysqli_num_rows($orderRes) > 0):
                              while($order = mysqli_fetch_assoc($orderRes)):
                          ?>
                            <tr>
                              <td><?= htmlspecialchars($order['created_at']) ?></td>
                              <td><?= number_format($order['total'], 2) ?> EGP</td>
                            </tr>
                          <?php endwhile; else: ?>
                            <tr><td colspan="2" class="text-muted">No orders found.</td></tr>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>

              <?php endwhile; ?>
            </tbody>
          </table>

          <?php if ($totalPages > 1): ?>
            <nav aria-label="Order pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <?php
                        $prev_page_params = $_GET;
                        $prev_page_params['page'] = $page - 1;
                        if ($prev_page_params['page'] < 1) unset($prev_page_params['page']);
                        ?>
                        <a class="page-link" href="?<?= http_build_query($prev_page_params) ?>" aria-label="Previous">
                            <span aria-hidden="true">Prev</span>
                        </a>
                    </li>
                    
                    <?php
                    $range = 2; 
                    $start_page = max(1, $page - $range);
                    $end_page = min($totalPages, $page + $range);
                    
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
                        
                        echo '<li class="page-item ' . (($i == $page) ? 'active' : '') . '">
                                <a class="page-link" href="?' . http_build_query($page_params) . '">' . $i . '</a>
                              </li>';
                    }
                    
                    if ($end_page < $totalPages) {
                        if ($end_page < $totalPages - 1) {
                            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                        }
                        $last_page_params = $_GET;
                        $last_page_params['page'] = $totalPages;
                        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($last_page_params) . '">' . $totalPages . '</a></li>';
                    }
                    ?>
                    
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <?php
                        $next_page_params = $_GET;
                        $next_page_params['page'] = $page + 1;
                        ?>
                        <a class="page-link" href="?<?= http_build_query($next_page_params) ?>" aria-label="Next">
                            <span aria-hidden="true">Next</span>
                        </a>
                    </li>
                </ul>
            </nav>
          <?php endif; ?>

        <?php else: ?>
          <div class="alert alert-info text-center">
            No users found.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include '../../includes/footer.php'; ?>