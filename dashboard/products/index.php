<?php
include '../../includes/header.php';
include '../../db/connect.php';

$nameFilter   = trim($_GET['name']      ?? '');
$minPrice     = trim($_GET['min_price'] ?? '');
$maxPrice     = trim($_GET['max_price'] ?? '');
$catFilter    = trim($_GET['category']  ?? '');

$where = [];
if ($nameFilter !== '') {
    $where[] = "name LIKE '%" . mysqli_real_escape_string($conn, $nameFilter) . "%'";
}
if ($minPrice !== '') {
    $where[] = "price >= " . (float)$minPrice;
}
if ($maxPrice !== '') {
    $where[] = "price <= " . (float)$maxPrice;
}
if ($catFilter !== '') {
    $where[] = "category_id = " . (int)$catFilter;
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$limit  = 5;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) AS total FROM products $whereSQL";
$countRes = mysqli_query($conn, $countSql);
$totalRow = mysqli_fetch_assoc($countRes);
$totalProducts = $totalRow['total'];
$totalPages    = ceil($totalProducts / $limit);

$sql    = "SELECT * FROM products $whereSQL ORDER BY id ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

$catSql = "SELECT id, name FROM categories";
$catRes = mysqli_query($conn, $catSql);

$params = $_GET;
unset($params['page']);
$baseQS = http_build_query($params);
?>

<style>
.pagination .page-link { border: none; padding: 0.5rem 0.75rem; transition: background 0.2s; }
.pagination .page-item.active .page-link { background-color: #0d6efd; color: #fff; }
.pagination .page-link:hover { background-color: #0d6efd; color: #fff; }
</style>

<div class="container w-75 mt-5">
  <h1 class="mb-5">Admin products dashboard</h1>
  
  <form method="get" class="row g-2 mb-4">
    <div class="col-sm">
      <input type="text" name="name" class="form-control" placeholder="Search by name" value="<?=htmlspecialchars($nameFilter)?>">
    </div>
    <div class="col-auto">
      <input type="number" name="min_price" class="form-control" placeholder="Min price" value="<?=htmlspecialchars($minPrice)?>">
    </div>
    <div class="col-auto">
      <input type="number" name="max_price" class="form-control" placeholder="Max price" value="<?=htmlspecialchars($maxPrice)?>">
    </div>
    <div class="col-auto">
      <select name="category" class="form-select">
        <option value="">All categories</option>
        <?php while($cat = mysqli_fetch_assoc($catRes)): ?>
          <option value="<?=$cat['id']?>" <?= $cat['id'] == $catFilter ? 'selected' : '' ?>>
            <?=htmlspecialchars($cat['name'])?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">Filter</button>
    </div>
  </form>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: white; color: #000;">
            <h5 class="m-2 fs-5">All Products</h5>
            <a href="add_product.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add New Product</a>
        </div>
      <div class="table-responsive">
        <table class="table table-striped table-hover mt-3 text-center align-middle">
          <tr>
            <th>Product</th><th>Price</th><th>Image</th><th colspan="3">Action</th>
          </tr>
          <?php while($product = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?=htmlspecialchars($product['name'])?></td>
              <td><?=htmlspecialchars($product['price'])?> EGP</td>
              <td><img src="../../assets/images/products/<?=htmlspecialchars($product['image'])?>" alt="" width="50" height="50" style="object-fit:cover; border-radius:8px"></td>
              <td><a href="available_product.php?product_id=<?=$product['id']?>" class="btn btn-outline-success border-0"><i class="fas fa-check fs-5"></i></a></td>
              <td><a href="edit_product.php?product_id=<?=$product['id']?>" class="btn btn-outline-warning border-0"><i class="fas fa-edit fs-5"></i></a></td>
              <td><a href="delete_product.php?product_id=<?=$product['id']?>" class="btn btn-outline-danger border-0"><i class="fas fa-trash fs-5"></i></a></td>
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
            <?php if($start > 2): ?>
              <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
          <?php endif; ?>

          <?php for($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?=$i == $page ? 'active' : ''?>">
              <a class="page-link rounded-pill" href="?<?=$baseQS?>&page=<?=$i?>"><?=$i?></a>
            </li>
          <?php endfor; ?>

          <?php if($end < $totalPages): ?>
            <?php if($end < $totalPages - 1): ?>
              <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
            <li class="page-item"><a class="page-link rounded-pill" href="?<?=$baseQS?>&page=<?=$totalPages?>"><?=$totalPages?></a></li>
          <?php endif; ?>

          <li class="page-item <?=$page == $totalPages ? 'disabled' : ''?>">
            <a class="page-link rounded-pill ms-2" href="?<?=$baseQS?>&page=<?=$page+1?>"><i class="fas fa-chevron-right"></i></a>
          </li>

        </ul>
      </nav>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
