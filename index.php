<?php
session_start();

/**
 * 
 * ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
 * 
 */

include './includes/header.php';
include './db/connect.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity   = (int)$_POST['quantity'];
    if ($quantity > 0) {
        $_SESSION['cart'][$product_id] = $quantity;
    } else {
        unset($_SESSION['cart'][$product_id]);
    }
    echo json_encode(['success' => true]);
    exit;
}

$products_per_page = 5;
$page  = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$offset = ($page - 1) * $products_per_page;

$count_result   = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
$total_products = mysqli_fetch_assoc($count_result)['total'];
$total_pages    = ceil($total_products / $products_per_page);

$products = [];
$result   = mysqli_query($conn, "SELECT * FROM products LIMIT $offset, $products_per_page");
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

$all_products = [];
$res_prices   = mysqli_query($conn, "SELECT id, price, name FROM products");
while ($row = mysqli_fetch_assoc($res_prices)) {
    $all_products[] = $row;
}

$list_of_users = [];
$res_users     = mysqli_query($conn, "SELECT id, name FROM users");
while ($row = mysqli_fetch_assoc($res_users)) {
    $list_of_users[] = $row;
}

$rooms = [];
$res_rooms = mysqli_query($conn, "SELECT id, name FROM rooms");
while ($row = mysqli_fetch_assoc($res_rooms)) {
    $rooms[] = $row;
}

if (isset($_POST["btn"])) {
    $user_id    = $_POST["user_id"] ?? $_SESSION['user']['id'];
    $room_id    = $_POST["room_id"];
    $notes      = $_POST["notes"];
    $total      = $_POST["total"] ?? 0;

    if (empty($user_id) || empty($room_id)) {
        $error_message = "Please select both a user and a room.";
    } else {
        $query = "
          INSERT INTO orders (user_id, room_id, notes, total, status)
          VALUES (?, ?, ?, ?, 'processing')
        ";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iisd", $user_id, $room_id, $notes, $total);

        if (mysqli_stmt_execute($stmt)) {
            $order_id = mysqli_insert_id($conn);
            $order_items_added = false;

            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                if ($quantity > 0) {
                    $iq = "
                      INSERT INTO order_products (order_id, product_id, quantity)
                      VALUES (?, ?, ?)
                    ";
                    $ist = mysqli_prepare($conn, $iq);
                    mysqli_stmt_bind_param($ist, "iii", $order_id, $product_id, $quantity);
                    mysqli_stmt_execute($ist);
                    $order_items_added = true;
                }
            }

            if ($order_items_added) {
                $_SESSION['cart'] = [];  
                header("Location: user_orders.php");
                exit();
            } else {
                mysqli_query($conn, "DELETE FROM orders WHERE id = $order_id");
                $error_message = "Please select at least one product.";
            }
        } else {
            $error_message = "Error placing order: " . mysqli_error($conn);
        }
    }
}
?>

<style>
.product-card {
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.product-img-container {
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #eee;
}
.product-img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}
.product-body {
    padding: 1.25rem;
}
.quantity-controls {
    max-width: 140px;
    margin: 0 auto;
}
</style>

<div class="container mt-4">
  <?php if (isset($_SESSION['user'])): ?>
    Hello, <?= htmlspecialchars($_SESSION['user']['name']) ?>
  <?php else: ?>
    Hello, Guest
  <?php endif; ?>

  <h1 class="text-center mb-4">Place New Order</h1>

  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" id="orderForm">
    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm sticky-top" style="top:20px">
          <div class="card-body">
            <h4 class="card-title mb-3">Order Summary</h4>

            <?php if (isset($error_message)): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
              <div class="mb-3">
                <label for="user_id" class="form-label">Select User</label>
                <select name="user_id" id="user_id" class="form-select">
                  <option value="">Choose User</option>
                  <?php foreach ($list_of_users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endif; ?>

            <div class="mb-3">
              <label for="room_id" class="form-label">Select Room</label>
              <select name="room_id" id="room_id" class="form-select">
                <option value="">Choose Room</option>
                <?php foreach ($rooms as $r): ?>
                  <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="notes" class="form-label">Notes</label>
              <textarea name="notes" id="notes" class="form-control" rows="3"
                        placeholder="e.g., less sugar..."></textarea>
            </div>

            <div class="mb-3">
              <h4>Total: <span id="total_display">0.00</span> EGP</h4>
              <input type="hidden" id="total" name="total" value="0">
            </div>

            <button type="submit" class="btn btn-success w-100" name="btn">Confirm Order</button>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
          <?php foreach ($products as $p): ?>
            <div class="col">
              <div class="card h-100 shadow-sm product-card">
                <div class="product-img-container">
                <?php if (!empty($p['image'])): ?>
                  <img src="assets/images/products/<?= htmlspecialchars($p['image']) ?>"
                      class="product-img"
                      alt="<?= htmlspecialchars($p['name']) ?>">
                <?php else: ?>
                  <div class="text-muted">No image available</div>
                <?php endif ?>
                </div>
                <div class="product-body text-center">
                  <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
                  <p class="text-muted"><?= $p['price'] ?> EGP</p>
                  <?php if ($p['is_available'] == 0): ?>
                    <span class="badge bg-light text-dark">Not Available</span>
                  <?php else: ?>
                    <div class="input-group quantity-controls">
                      <button class="btn btn-outline-secondary"
                              onclick="updateQty(<?= $p['id'] ?>,-1)">-</button>
                      <input type="number"
                             id="qty-<?= $p['id'] ?>"
                             class="form-control text-center product-qty"
                             value="<?= $_SESSION['cart'][$p['id']] ?? 0 ?>"
                             readonly>
                      <button class="btn btn-outline-secondary"
                              onclick="updateQty(<?= $p['id'] ?>,1)">+</button>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if ($total_pages>1): ?>
        <nav aria-label="Page navigation" class="mt-4">
          <ul class="pagination justify-content-center">
            <li class="page-item <?= $page>1?'':'disabled' ?>">
              <a class="page-link" href="?page=<?= $page-1 ?>">prev</a>
            </li>
            <?php for($i=1;$i<=$total_pages;$i++): ?>
              <li class="page-item <?= $i===$page?'active':'' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= $page<$total_pages?'':'disabled' ?>">
              <a class="page-link" href="?page=<?= $page+1 ?>">next</a>
            </li>
          </ul>
        </nav>
        <?php endif; ?>
      </div>
    </div>
  </form>
</div>

<script>
  const cart = <?= json_encode($_SESSION['cart']) ?>;
  const prices = { <?php foreach($all_products as $x) echo "{$x['id']}:{$x['price']},"; ?> };
  const productNames = { <?php foreach($all_products as $x) echo "{$x['id']}:\"".addslashes($x['name'])."\","; ?> };

  function updateQty(id, change) {
    const input = document.getElementById(`qty-${id}`);
    let qty = parseInt(input.value) + change;
    qty = qty<0?0:qty;
    input.value = qty;
    cart[id] = qty;

    fetch(location.pathname, {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`update_qty=1&product_id=${id}&quantity=${qty}`
    })
    .then(r=>r.json())
    .then(d=>{
      if(d.success) {
        updateTotal();
        updateSelectedItems();
      }
    });
  }

  function updateTotal() {
    let total = 0;
    for (let id in cart) {
      const q = parseInt(cart[id])||0;
      total += (prices[id]||0)*q;
    }
    document.getElementById('total_display').innerText = total.toFixed(2);
    document.getElementById('total').value = total.toFixed(2);
  }

  function updateSelectedItems() {
    const c = document.getElementById('selected_items');
    c.innerHTML = '';
    let any=false;
    for (let id in cart) {
      const q = parseInt(cart[id])||0;
      if (q>0) {
        any=true;
        const div = document.createElement('div');
        div.className='list-group-item d-flex justify-content-between';
        div.innerHTML = `${productNames[id]}<span class="badge bg-primary rounded-pill">${q}×${prices[id]} EGP</span>`;
        c.appendChild(div);
      }
    }
    if (!any) {
      const div = document.createElement('div');
      div.className='list-group-item text-muted';
      div.innerText='No items selected';
      c.appendChild(div);
    }
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    updateTotal();
    updateSelectedItems();
  });
</script>

<?php include './includes/footer.php'; ?>