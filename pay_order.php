<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

if (isset($_POST['pay'])) {
  // Prevent Posting Blank Values
  if (empty($_POST["pay_code"]) || empty($_POST["pay_amt"]) || empty($_POST['pay_method'])) {
    $err = "Blank Values Not Accepted";
    // Perform Regex On Payments
  } else {
    $pay_Code = $_POST['pay_code'];

    if (strlen($pay_Code) !== 10) {
      $err = "Payment Code must be 10 characters long";
    } else {
      $pay_id = $_POST['pay_id'];
      $pay_code = $_POST['pay_code'];
      $order_code = $_GET['order_code'];
      $customer_id = $_GET['customer_id'];
      $pay_amt  = $_POST['pay_amt'];
      $pay_method = $_POST['pay_method'];
      $order_status = $_GET['order_status'];

      // Insert Captured information to a database table
      $postQuery = "INSERT INTO rpos_payments (pay_id, pay_code, order_code, customer_id, pay_amt, pay_method) VALUES (?, ?, ?, ?, ?, ?)";
      $upQry = "UPDATE rpos_orders SET order_status = ? WHERE order_code = ?";

      $postStmt = $mysqli->prepare($postQuery);
      $upStmt = $mysqli->prepare($upQry);
      
      // Check if prepares were successful
      if (!$postStmt || !$upStmt) {
        $err = "Prepare statement error: " . $mysqli->error;
      } else {
        // Bind parameters for payment insert
        $postStmt->bind_param('ssssss', $pay_id, $pay_code, $order_code, $customer_id, $pay_amt, $pay_method);
        $postStmt->execute();

        // Bind parameters for order update
        $upStmt->bind_param('ss', $order_status, $order_code);
        $upStmt->execute();

        // Check if both queries were executed successfully
        if ($upStmt->affected_rows > 0 && $postStmt->affected_rows > 0) {
          $success = "Paid";
          header("refresh:1; url=payments_reports.php");
        } else {
          $err = "Please Try Again Or Try Later";
        }
      }
    }
  }
}

require_once('partials/_head.php');
?>

<body>
  <!-- Sidenav -->
  <?php require_once('partials/_sidebar.php'); ?>
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php require_once('partials/_topnav.php'); ?>

    <?php
    $order_code = $_GET['order_code'];
    $ret = "SELECT o.*, p.prod_price FROM rpos_orders o JOIN rpos_products p ON o.prod_id = p.prod_id WHERE order_code = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $order_code);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($order = $res->fetch_object()) {
      // Ensure prod_price is numeric
      $prod_price = (float) $order->prod_price; // Cast to float
      
      // Calculate total price in INR
      $total = $prod_price * (float) $order->prod_qty; // Assuming prod_qty is also numeric

      // Convert total to INR (assuming 1 USD = 74.5 INR for example)
      $total_inr = $total * 74.5; // Replace with actual conversion rate

      // Format total to 2 decimal places for display
      $total_inr_formatted = number_format($total_inr, 2);
    ?>
      <!-- Header -->
      <div style="background-image: url(../admin/assets/img/theme/restro00.jpg); background-size: cover;" class="header  pb-8 pt-5 pt-md-8">
        <span class="mask bg-gradient-dark opacity-8"></span>
        <div class="container-fluid">
          <div class="header-body">
          </div>
        </div>
      </div>
      <!-- Page content -->
      <div class="container-fluid mt--8">
        <!-- Table -->
        <div class="row">
          <div class="col">
            <div class="card shadow">
              <div class="card-header border-0">
                <h3>Please Fill All Fields</h3>
              </div>
              <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                  <div class="form-row">
                    <div class="col-md-6">
                      <label>Payment ID</label>
                      <input type="text" name="pay_id" readonly value="<?php echo $payid; ?>" class="form-control">
                    </div>
                    <div class="col-md-6">
                      <label>Payment Code</label><small class="text-danger"> Type 10 Digits Alpha-Code If Payment Method Is In Cash</small>
                      <input type="text" limit="11" name="pay_code" placeholder="<?php echo $mpesaCode; ?>" class="form-control" value="">
                    </div>
                  </div>
                  <hr>
                  <div class="form-row">
                    <div class="col-md-6">
                      <label>Amount (INR)</label>
                      <input type="text" name="pay_amt" readonly value="<?php echo $total_inr_formatted; ?>" class="form-control">
                    </div>
                    <div class="col-md-6">
                      <label>Payment Method</label>
                      <select class="form-control" name="pay_method">
                        <option selected>Cash</option>
                        <option>Paypal</option>
                      </select>
                    </div>
                  </div>
                  <br>
                  <div class="form-row">
                    <div class="col-md-6">
                      <input type="submit" name="pay" value="Pay Order" class="btn btn-success" value="">
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- Footer -->
        <?php require_once('partials/_footer.php'); ?>
      </div>
  </div>
  <!-- Argon Scripts -->
  <?php require_once('partials/_scripts.php');
  }
  ?>
</body>

</html>
