<?php
    include '../../includes/header.php';
    include '../../db/connect.php';

    
    $where = [];
    if (!empty($_GET['from_date'])) {
        $from = mysqli_real_escape_string($conn, $_GET['from_date']);
        $where[] = "order_date >= '$from'";
    }
    if (!empty($_GET['to_date'])) {
        $to = mysqli_real_escape_string($conn, $_GET['to_date']);
        $where[] = "order_date <= '$to'";
    }
    if (!empty($_GET['user_id'])) {
        $uid = (int)$_GET['user_id'];
        $where[] = "user_id = $uid";
    }
    $whereSQL = $where ? 'WHERE '.implode(' AND ', $where) : '';

    
    $sql = "
      SELECT c.id, c.order_date, c.total, c.user_id, u.name 
      FROM checks c
      JOIN users u ON u.id = c.user_id
      $whereSQL
      ORDER BY c.order_date DESC
    ";
    $res = mysqli_query($conn, $sql);
?>

<div class="container w-75 mt-5">
  <h1 class="text-center text-muted">Admin checks dashboard</h1>

  <!-- Filter Form -->
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-3">
      <label>From:</label>
      <input type="date" name="from_date" class="form-control"
             value="<?=htmlspecialchars($_GET['from_date'] ?? '')?>">
    </div>
    <div class="col-md-3">
      <label>To:</label>
      <input type="date" name="to_date" class="form-control"
             value="<?=htmlspecialchars($_GET['to_date'] ?? '')?>">
    </div>
    <div class="col-md-4">
      <label>User:</label>
      <select name="user_id" class="form-select">
        <option value="">All users</option>
        <?php
          $usrRs = mysqli_query($conn, "SELECT id,name FROM users");
          while($u = mysqli_fetch_assoc($usrRs)) {
            $sel = (($_GET['user_id'] ?? '') == $u['id']) ? 'selected' : '';
            echo "<option value=\"{$u['id']}\" $sel>"
               . htmlspecialchars($u['name'])
               . "</option>";
          }
        ?>
      </select>
    </div>
    <div class="col-md-2 align-self-end">
      <button class="btn btn-primary w-100">Filter</button>
    </div>
  </form>


  <table class="table">
    <thead>
      <tr><th>Date</th><th>User</th><th>Total</th></tr>
    </thead>
    <tbody>
      <?php while($row = mysqli_fetch_assoc($res)): ?>
        <tr>
          <td><?=htmlspecialchars($row['order_date'])?></td>
          <td>
            <a href="../orders/index.php?
                     user_id=<?= $row['user_id'] ?>
                     &from_date=<?= urlencode($_GET['from_date'] ?? '') ?>
                     &to_date=<?= urlencode($_GET['to_date'] ?? '') ?>">
              <?=htmlspecialchars($row['name'])?>
            </a>
          </td>
          <td><?=htmlspecialchars($row['total'])?> EGP</td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</div>

<?php include '../../includes/footer.php'; ?>
