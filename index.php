<?php
    include './includes/header.php';
    include './db/connect.php';

    $products = [];
    $result = mysqli_query($conn, "SELECT * FROM products");
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    $list_of_users = [];
    $result = mysqli_query($conn, "SELECT * FROM users");
    while ($row = mysqli_fetch_assoc($result)) {
        $list_of_users[] = $row;
    }
    $rooms = ['Room 101', 'Room 102', 'Room 103'];// not real
    // this room willbe modified to be dynamic
    if(isset($_POST["btn"])){
        $room = $_POST["room"];
        $notes = $_POST["notes"];
        $quantities = $_POST["qty"] ?? [];
        $total = $_POST["total"] ?? 0;
        
        $user_id = $_SESSION['user_id'] ?? 1; // => todo: get user id from session only
        
        $query = "INSERT INTO orders (user_id, notes, total, status) VALUES (?, ?, ?, 'processing')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "isd", $user_id, $notes, $total);
        
        if(mysqli_stmt_execute($stmt)) {
            $order_id = mysqli_insert_id($conn);
            
            $order_items_added = false;
            foreach($quantities as $product_id => $quantity) {
                if($quantity > 0) {

                    //$sql = "INSERT INTO order_products (order_id, product_id, quantity) VALUES ($order_id, $product_id, $quantity)"; 
                    //mysqli_query($conn, $sql);
                    
                    // but this good for sql injection
                    $insert_query = "INSERT INTO order_products (order_id, product_id, quantity) VALUES (?, ?, ?)";
                    $insert_stmt = mysqli_prepare($conn, $insert_query);
                    mysqli_stmt_bind_param($insert_stmt, "iii", $order_id, $product_id, $quantity);
                    mysqli_stmt_execute($insert_stmt);
                    $order_items_added = true;
                }
            }
            
            if($order_items_added) {
                echo "<div class='container'>
                    <div class='alert alert-success'>Order placed successfully!</div>
                </div>";
                // header("Location: orders.php");
            } else {
                mysqli_query($conn, "DELETE FROM orders WHERE id = $order_id");
                echo "<div class='alert alert-warning'>Please select at least one product to order.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Error placing order: " . mysqli_error($conn) . "</div>";
        }
    }
?>
<div class="container mt-4">
    <h1 class="text-center mb-4">Place New Order</h1>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" id="orderForm">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Order Summary</h4>
                        <!--
                            we will add here list of user if logged one is an admin
                        -->
                        <div class="mb-3">
                            <label for="choosenUser" class="form-label">Select User</label>
                            <select name="choosenUser" id="choosenUser" class="form-select">
                                <option value="">Choose User</option>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?= $room ?>"><?= $room ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="room" class="form-label">Select Room</label>
                            <select name="room" id="room" class="form-select">
                                <option value="">Choose Room</option>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?= $room ?>"><?= $room ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="e.g., less sugar, extra hot..."></textarea>
                        </div>

                        <div class="mb-3">
                            <h4>Total: <span id="total_display">0</span> EGP</h4>
                            <input type="hidden" id="total" name="total" value="0">
                        </div>

                        <button type="submit" class="btn btn-success w-100" name="btn" id="orderBtn">Confirm Order</button>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($products as $product): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-truncate"><?= $product['name'] ?></h5>
                                    <p class="text-muted"><?= $product['price'] ?> EGP</p>
                                    <div class="input-group mb-2">
                                        <button type="button" class="btn btn-outline-secondary" onclick="updateQty(<?= $product['id'] ?>, -1)">-</button>
                                        <input type="number" name="qty[<?= $product['id'] ?>]" id="qty-<?= $product['id'] ?>" class="form-control text-center product-qty" value="0" min="0" readonly>
                                        <button type="button" class="btn btn-outline-secondary" onclick="updateQty(<?= $product['id'] ?>, 1)">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="errorModalLabel">Order Validation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="errorModalBody">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
    const prices = {
        <?php foreach ($products as $p): ?>
        <?= $p['id'] ?>: <?= $p['price'] ?>,
        <?php endforeach; ?>
    };

    function showErrorModal(message) {
        document.getElementById('errorModalBody').innerHTML = message;
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            let hasProducts = false;
            document.querySelectorAll('.product-qty').forEach(function(input) {
                if (parseInt(input.value) > 0) {
                    hasProducts = true;
                }
            });
            
            if (!hasProducts) {
                e.preventDefault();
                showErrorModal('<div class="alert alert-warning">Please select at least one product to order.</div>');
                return false;
            }
            
            if (!document.getElementById('room').value) {
                e.preventDefault();
                showErrorModal('<div class="alert alert-warning">Please select a room.</div>');
                return false;
            }
        });
    });

    function updateQty(id, change) {
        const input = document.getElementById(`qty-${id}`);
        let qty = parseInt(input.value) + change;
        qty = qty < 0 ? 0 : qty;
        input.value = qty;
        updateTotal();
    }

    function updateTotal() {
        let total = 0;
        for (const id in prices) {
            const qty = parseInt(document.getElementById(`qty-${id}`).value) || 0;
            total += prices[id] * qty;
        }
        document.getElementById('total_display').innerText = total;
        document.getElementById('total').value = total;
    }
</script>

<?php include './includes/footer.php'; ?>