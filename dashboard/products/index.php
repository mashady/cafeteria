<?php
include '../../includes/header.php';
include '../../db/connect.php';
include '../../includes/admin_auth.php';

if (isset($_GET['toggle_availability'])) {
    $productId = (int)$_GET['product_id'];
    $currentStatus = (int)$_GET['current_status'];
    $newStatus = $currentStatus ? 0 : 1;
    
    $updateSql = "UPDATE products SET is_available = $newStatus WHERE id = $productId";
    if (mysqli_query($conn, $updateSql)) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'new_status' => $newStatus]);
            exit;
        }
        header("Location: ".strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
}

$nameFilter = trim($_GET['name'] ?? '');
$minPrice = trim($_GET['min_price'] ?? '');
$maxPrice = trim($_GET['max_price'] ?? '');
$catFilter = trim($_GET['category'] ?? '');
$availabilityFilter = isset($_GET['availability']) ? (int)$_GET['availability'] : null;

$where = [];
if ($nameFilter !== '') {
    $where[] = "p.name LIKE '%" . mysqli_real_escape_string($conn, $nameFilter) . "%'";
}
if ($minPrice !== '') {
    $where[] = "p.price >= " . (float)$minPrice;
}
if ($maxPrice !== '') {
    $where[] = "p.price <= " . (float)$maxPrice;
}
if ($catFilter !== '') {
    $where[] = "p.category_id = " . (int)$catFilter;
}
if ($availabilityFilter !== null) {
    $where[] = "p.is_available = " . (int)$availabilityFilter;
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get total count
$countSql = "SELECT COUNT(*) AS total FROM products p $whereSQL";
$countRes = mysqli_query($conn, $countSql);
$totalRow = mysqli_fetch_assoc($countRes);
$totalProducts = $totalRow['total'];
$totalPages = ceil($totalProducts / $limit);

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $whereSQL 
        ORDER BY p.id ASC 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// Get categories for dropdown
$catSql = "SELECT id, name FROM categories";
$catRes = mysqli_query($conn, $catSql);

$params = $_GET;
unset($params['page'], $params['toggle_availability'], $params['product_id'], $params['current_status']);
$baseQS = http_build_query($params);
?>

<script>
function toggleAvailability(productId, currentStatus) {
    const newStatus = currentStatus === 1 ? 0 : 1;
    
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = window.location.href.split('?')[0];
    
    const params = new URLSearchParams(window.location.search);
    params.forEach((value, key) => {
        if (key !== 'toggle_availability' && key !== 'product_id' && key !== 'current_status') {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }
    });
    
    const toggleInput = document.createElement('input');
    toggleInput.type = 'hidden';
    toggleInput.name = 'toggle_availability';
    toggleInput.value = '1';
    form.appendChild(toggleInput);
    
    const productInput = document.createElement('input');
    productInput.type = 'hidden';
    productInput.name = 'product_id';
    productInput.value = productId;
    form.appendChild(productInput);
    
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'current_status';
    statusInput.value = currentStatus;
    form.appendChild(statusInput);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<style>
.card-header { background-color: #0d6efd; color: white; }
.table th { background-color: #f8f9fa; }
.pagination .page-link { border: none; }
.pagination .page-item.active .page-link { background-color: #0d6efd; }
.availability-checkbox { width: 20px; height: 20px; cursor: pointer; }
.form-check-input:checked { background-color: #198754; border-color: #198754; }
.filter-section {  padding: 15px; border-radius: 5px; margin-bottom: 20px; }
</style>

<div class='container w-75 mt-5'>
    <h1 class="mb-3">Admin Products Dashboard</h1>

    <div class="filter-section">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" placeholder="Search by name" 
                       value="<?=htmlspecialchars($nameFilter)?>">
            </div>
            
            <div class="col-md-3">
                <label for="min_price" class="form-label">Min Price</label>
                <input type="number" name="min_price" class="form-control" placeholder="Min price" 
                       value="<?=htmlspecialchars($minPrice)?>">
            </div>
            
            <div class="col-md-3">
                <label for="max_price" class="form-label">Max Price</label>
                <input type="number" name="max_price" class="form-control" placeholder="Max price" 
                       value="<?=htmlspecialchars($maxPrice)?>">
            </div>
            
            <div class="col-md-3">
                <label for="category" class="form-label">Category</label>
                <select name="category" class="form-select">
                    <option value="">All categories</option>
                    <?php 
                    mysqli_data_seek($catRes, 0);
                    while($cat = mysqli_fetch_assoc($catRes)): ?>
                        <option value="<?=$cat['id']?>" <?= $cat['id'] == $catFilter ? 'selected' : '' ?>>
                            <?=htmlspecialchars($cat['name'])?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
          
            
            <div class="col-md-12 d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-outline-primary btn-sm me-2">
                     Filters
                </button>
                <a href="?" class="btn btn-outline-secondary btn-sm">
                     Clear
                </a>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="add_product.php" class="btn btn-primary btn-sm">
                 Add New Product
            </a>
        </div>
        <div class="text-muted">
            Total Products: <?= $totalProducts ?>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped mt-3 align-middle table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Image</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($product = mysqli_fetch_assoc($result)): 
                            $id = htmlspecialchars($product['id']);
                            $isAvailable = isset($product['is_available']) ? (int)$product['is_available'] : 1;
                        ?>
                            <tr>
                                <td><?=htmlspecialchars($product['name'])?></td>
                                <td><?=htmlspecialchars($product['category_name'] ?? 'Uncategorized')?></td>
                                <td><?=number_format($product['price'], 2)?> EGP</td>
                                <td>
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="../../assets/images/products/<?=htmlspecialchars($product['image'])?>" 
                                             width="50" height="50" 
                                             style="object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="form-check form-switch d-flex justify-content-center align-items-center">
                                        <input class="form-check-input availability-checkbox" type="checkbox" 
                                               role="switch" 
                                               id="availability-<?=$id?>" 
                                               <?= $isAvailable === 1 ? 'checked' : '' ?>
                                               onclick="toggleAvailability(<?=$id?>, <?=$isAvailable?>)">
                                    </div>
                                </td>
                                <td>
                                    <a href="edit_product.php?product_id=<?=$id?>" class="btn btn-sm btn-outline-primary me-2">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?=$id?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="deleteModal<?=$id?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Product</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Confirm deletion of <strong><?=htmlspecialchars($product['name'])?></strong>?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="delete_product.php?product_id=<?=$id?>" class="btn btn-danger">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?=$baseQS?>&page=<?=$page-1?>">
                                prev
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?=$baseQS?>&page=<?=$i?>"><?=$i?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page == $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?=$baseQS?>&page=<?=$page+1?>">
                                next
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>