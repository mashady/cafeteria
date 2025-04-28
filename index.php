<?php

    include './includes/header.php';
    include './db/connect.php';

    $products = [];
    $result = mysqli_query($conn, "SELECT * FROM products"); // handle what appear here => product disable qty btn 
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    $list_of_users = [];
    $result = mysqli_query($conn, "SELECT id, name FROM users");
    while ($row = mysqli_fetch_assoc($result)) {
        $list_of_users[] = $row;
    }

    $rooms = [];
    $result = mysqli_query($conn, "SELECT id, name FROM rooms");
    while ($row = mysqli_fetch_assoc($result)) {
        $rooms[] = $row;
    }

    if (isset($_POST["btn"])) {
        $user_id = $_POST["user_id"] ?? $_SESSION['user']['id'];
        $room_id = $_POST["room_id"]; 
        $notes = $_POST["notes"];
        $quantities = $_POST["qty"] ?? [];
        $total = $_POST["total"] ?? 0;

        if (empty($user_id) || empty($room_id)) {
            $error_message = "Please select both a user and a room.";
        } else {
            $query = "INSERT INTO orders (user_id, room_id, notes, total, status) VALUES (?, ?, ?, ?, 'processing')";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "iisd", $user_id, $room_id, $notes, $total);

            if (mysqli_stmt_execute($stmt)) {
                $order_id = mysqli_insert_id($conn);

                $order_items_added = false;
                foreach ($quantities as $product_id => $quantity) {
                    if ($quantity > 0) {
                        $insert_query = "INSERT INTO order_products (order_id, product_id, quantity) VALUES (?, ?, ?)";
                        $insert_stmt = mysqli_prepare($conn, $insert_query);
                        mysqli_stmt_bind_param($insert_stmt, "iii", $order_id, $product_id, $quantity);
                        mysqli_stmt_execute($insert_stmt);
                        $order_items_added = true;
                    }
                }

                if ($order_items_added) {
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

<div class="container mt-4">
<?php
if (isset($_SESSION['user'])) {
    echo "Hello, " . $_SESSION['user']['name'];
} else {
    echo "Hello, Guest";
}
?>

    <h1 class="text-center mb-4">Place New Order</h1>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" id="orderForm">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
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
                                    <?php foreach ($list_of_users as $user): ?>
                                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif ?>


                        <div class="mb-3">
                            <label for="room_id" class="form-label">Select Room</label>
                            <select name="room_id" id="room_id" class="form-select">
                                <option value="">Choose Room</option>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?= $room['id'] ?>"><?= htmlspecialchars($room['name']) ?></option>
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
                                    

                                    <div class="card-body text-center">

                                <?php if ($product['is_available'] == 0): ?>
                                    <span class="badge bg-light text-dark">Not Available</span>
                                <?php else: ?>
                                    <div class="input-group mb-2">
                                        <button type="button" class="btn btn-outline-secondary" onclick="updateQty(<?= $product['id'] ?>, -1)">-</button>
                                        <input type="number" name="qty[<?= $product['id'] ?>]" id="qty-<?= $product['id'] ?>" class="form-control text-center product-qty" value="0" min="0" readonly>
                                        <button type="button" class="btn btn-outline-secondary" onclick="updateQty(<?= $product['id'] ?>, 1)">+</button>
                                    </div>
                                <?php endif; ?>
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

<script>
    const prices = {
        <?php foreach ($products as $p): ?>
        <?= $p['id'] ?>: <?= $p['price'] ?>,
        <?php endforeach; ?>
    };

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
