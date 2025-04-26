
<?php
include '../../includes/header.php';
include '../../db/connect.php';

// ——— Read filters from GET
$nameFilter   = trim($_GET['name']   ?? '');
$roomFilter   = trim($_GET['room']   ?? '');
$extFilter    = trim($_GET['ext']    ?? '');
$roleFilter   = trim($_GET['role']   ?? '');

// ——— Build dynamic WHERE conditions
$where = [];
if ($nameFilter !== '') {
    $where[] = "name LIKE '%" . mysqli_real_escape_string($conn, $nameFilter) . "%'";
}
if ($roomFilter !== '') {
    $where[] = "room = '" . mysqli_real_escape_string($conn, $roomFilter) . "'";
}
if ($extFilter !== '') {
    $where[] = "ext = '" . mysqli_real_escape_string($conn, $extFilter) . "'";
}
if ($roleFilter !== '') {
    $where[] = "role = '" . mysqli_real_escape_string($conn, $roleFilter) . "'";
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// 1) pagination setup
$limit  = 5;  // users per page
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// 2) total count with filters
$countSql = "SELECT COUNT(*) AS total FROM users $whereSQL";
$countRes = mysqli_query($conn, $countSql);
$totalRow = mysqli_fetch_assoc($countRes);
$totalUsers = $totalRow['total'];
$totalPages = ceil($totalUsers / $limit);

// 3) fetch filtered & paginated users
$sql    = "SELECT * FROM users $whereSQL ORDER BY id ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// 4) preserve GET params for pagination links
$params = $_GET;
unset($params['page']);
$baseQS = http_build_query($params);
?>

<style>
.pagination .page-link { border: none; padding: 0.5rem 0.75rem; transition: background 0.2s; }
.pagination .page-item.active .page-link { background-color: #0d6efd; color: #fff; }
.pagination .page-link:hover { background-color: #0d6efd; color: #fff; }
</style>

<div class='container w-75 mt-5'>
    <h1 class="text-center text-muted mb-3">Admin Users dashboard</h1>

    <!-- Filter Form -->
    <form method="get" class="row g-2 mb-4">
        <div class="col-md">
            <input type="text" name="name" class="form-control" placeholder="Search by name" value="<?=htmlspecialchars($nameFilter)?>">
        </div>
        <div class="col-md">
            <select name="room" class="form-select">
                <option value="">All rooms</option>
                <?php for($r=1; $r<=5; $r++): ?>
                    <option value="<?= $r ?>" <?= $roomFilter == "$r" ? 'selected' : '' ?>>Room <?= $r ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md">
            <input type="text" name="ext" class="form-control" placeholder="Search by ext" value="<?=htmlspecialchars($extFilter)?>">
        </div>
        <div class="col-md">
            <select name="role" class="form-select">
                <option value="">All roles</option>
                <option value="user" <?= $roleFilter=='user' ? 'selected' : ''?>>User</option>
                <option value="admin" <?= $roleFilter=='admin' ? 'selected' : ''?>>Admin</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center ">
            <h5 class="m-2 fs-5">All Users</h5>
            <a href="add_user.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New User
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class='table table-striped mt-3 text-center align-middle'>
                    <tr>
                        <th>Name</th><th>Room</th><th>Image</th><th>Ext</th><th>Role</th><th colspan="2">Action</th>
                    </tr>
                    <?php while($users = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?=htmlspecialchars($users['name'])?></td>
                            <td><?=htmlspecialchars($users['room'])?></td>
                            <td><img src='../../assets/images/users/<?=htmlspecialchars($users['profile_pic'])?>' width='50' height='50' style='object-fit:cover;border-radius:8px'></td>
                            <td><?=htmlspecialchars($users['ext'])?></td>
                            <td><?=htmlspecialchars($users['role'])?></td>
                            <td><a href='edit.php?userid=<?=$users['id']?>' class='btn btn-outline-warning border-0'><i class='fas fa-edit fs-5'></i></a></td>
                            <td><a href='delete.php?userid=<?=$users['id']?>' class='btn btn-outline-danger border-0'><i class='fas fa-trash fs-5'></i></a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <?php
                $adjacents = 2;
                $start     = max(1, $page - $adjacents);
                $end       = min($totalPages, $page + $adjacents);
            ?>

            <nav aria-label="Page navigation">
                <ul class="pagination pagination-lg justify-content-center">
                    <li class="page-item <?=$page == 1 ? 'disabled' : ''?>">
                        <a class="page-link rounded-pill me-2" href="?<?=$baseQS?>&page=<?=$page-1?>"><i class="fas fa-chevron-left"></i></a>
                    </li>
                    <?php if($start > 1): ?>
                        <li class="page-item"><a class="page-link rounded-pill" href="?<?=$baseQS?>&page=1">1</a></li>
                        <?php if($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                    <?php endif; ?>
                    <?php for($i=$start; $i<=$end; $i++): ?>
                        <li class="page-item <?=$i==$page?'active':''?>"><a class="page-link rounded-pill" href="?<?=$baseQS?>&page=<?=$i?>"><?=$i?></a></li>
                    <?php endfor; ?>
                    <?php if($end < $totalPages): ?>
                        <?php if($end < $totalPages-1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                        <li class="page-item"><a class="page-link rounded-pill" href="?<?=$baseQS?>&page=<?=$totalPages?>"><?=$totalPages?></a></li>
                    <?php endif; ?>
                    <li class="page-item <?=$page==$totalPages?'disabled':''?>">
                        <a class="page-link rounded-pill ms-2" href="?<?=$baseQS?>&page=<?=$page+1?>"><i class="fas fa-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
```
