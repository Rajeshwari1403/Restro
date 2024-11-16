<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

if (isset($_POST['make'])) {
    // Prevent Posting Blank Values
    if (empty($_POST["order_code"]) || empty($_POST["customer_name"]) || empty($_POST['prod_price']) || empty($_POST['prod_qty'])) {
        $err = "Blank Values Not Accepted";
    } else {
        $order_id = $_POST['order_id'];
        $order_code  = $_POST['order_code'];
        $customer_id = $_SESSION['customer_id'];
        $customer_name = $_POST['customer_name'];
        $prod_id  = $_POST['prod_id'];
        $prod_name = $_POST['prod_name'];
        $prod_price = $_POST['prod_price'];
        $prod_qty = $_POST['prod_qty'];

        // Insert Captured information to a database table
        $postQuery = "INSERT INTO rpos_orders (prod_qty, order_id, order_code, customer_id, customer_name, prod_id, prod_name, prod_price) VALUES(?,?,?,?,?,?,?,?)";
        $postStmt = $mysqli->prepare($postQuery);
        // bind parameters
        $rc = $postStmt->bind_param('ssssssss', $prod_qty, $order_id, $order_code, $customer_id, $customer_name, $prod_id, $prod_name, $prod_price);
        $postStmt->execute();
        // declare a variable which will be passed to alert function
        if ($postStmt) {
            $success = "Order Submitted" && header("refresh:1; url=payments.php");
        } else {
            $err = "Please Try Again Or Try Later";
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
        <!-- Header -->
        <div style="background-image: url(../admin/assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
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
                                        <label>Customer Name</label>
                                        <?php
                                        // Load Customer Name from Session
                                        $customer_id = $_SESSION['customer_id'];
                                        $ret = "SELECT * FROM  rpos_customers WHERE customer_id = ?";
                                        $stmt = $mysqli->prepare($ret);
                                        $stmt->bind_param('s', $customer_id);
                                        $stmt->execute();
                                        $res = $stmt->get_result();
                                        while ($cust = $res->fetch_object()) {
                                        ?>
                                            <input class="form-control" readonly name="customer_name" value="<?php echo $cust->customer_name; ?>">
                                        <?php } ?>
                                        <input type="hidden" name="order_id" value="<?php echo $orderid; ?>" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Order Code</label>
                                        <input type="text" readonly name="order_code" value="<?php echo $alpha; ?>-<?php echo $beta; ?>" class="form-control" value="">
                                    </div>
                                </div>
                                <hr>
                                <?php
                                // Fetch Product Information
                                if (isset($_GET['prod_id'])) {
                                    $prod_id = $_GET['prod_id'];
                                    $ret = "SELECT * FROM rpos_products WHERE prod_id = ?";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->bind_param('s', $prod_id);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($prod = $res->fetch_object()) {
                                ?>
                                        <div class="form-row">
                                            <div class="col-md-6">
                                                <label>Product Name</label>
                                                <input type="text" readonly name="prod_name" value="<?php echo $prod->prod_name; ?>" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label>Product Price (₹)</label>
                                                <input type="text" readonly name="prod_price" value="<?php echo $prod->prod_price; ?>" class="form-control">
                                                <input type="hidden" name="prod_id" value="<?php echo $prod->prod_id; ?>">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="col-md-6">
                                                <label>Product Quantity</label>
                                                <input type="text" name="prod_qty" class="form-control" value="">
                                            </div>
                                        </div>
                                <?php
                                    }
                                } else {
                                    echo "No product selected.";
                                }
                                ?>
                                <br>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <input type="submit" name="make" value="Make Order" class="btn btn-success" value="">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Add Button Section -->
            <div class="row mt-4">
                <div class="col">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <h3>Add Product to Order</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Select Product</label>
                                        <select class="form-control" name="prod_id">
                                            <?php
                                            // Fetch all products
                                            $ret = "SELECT * FROM rpos_products";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            while ($prod = $res->fetch_object()) {
                                                echo "<option value='" . $prod->prod_id . "'>" . $prod->prod_name . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary mt-4">Add to Order</button>
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
    <?php require_once('partials/_scripts.php'); ?>
</body>

</html>
