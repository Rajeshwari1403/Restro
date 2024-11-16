<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');
?>

<body>
    <!-- Sidenav -->
    <?php
    require_once('partials/_sidebar.php');
    ?>
    <!-- Main content -->
    <div class="main-content">
        <!-- Top navbar -->
        <?php
        require_once('partials/_topnav.php');
        ?>
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
                            Order Reports
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-success" scope="col">Payment Code</th>
                                        <th scope="col">Payment Method</th>
                                        <th class="text-success" scope="col">Order Code</th>
                                        <th class="text-success" scope="col">Product Name</th>
                                        <th class="text-success" scope="col">Total Price (₹)</th>
                                        <th class="text-success" scope="col">Date Paid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $customer_id = $_SESSION['customer_id'];
                                    $ret = "
                                        SELECT 
                                            p.pay_code, 
                                            p.pay_method, 
                                            p.order_code, 
                                            p.created_at, 
                                            o.prod_name, 
                                            (o.prod_price * o.prod_qty) AS total_price 
                                        FROM 
                                            rpos_payments p
                                        JOIN 
                                            rpos_orders o ON p.order_code = o.order_code
                                        WHERE 
                                            p.customer_id = ? 
                                        ORDER BY 
                                            p.created_at DESC";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->bind_param('s', $customer_id);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($payment = $res->fetch_object()) {
                                        // Total price in rupees
                                        $total_price_inr = floatval($payment->total_price);
                                    ?>
                                        <tr>
                                            <th class="text-success" scope="row"><?php echo $payment->pay_code; ?></th>
                                            <th scope="row"><?php echo $payment->pay_method; ?></th>
                                            <td class="text-success"><?php echo $payment->order_code; ?></td>
                                            <td class="text-success"><?php echo $payment->prod_name; ?></td>
                                            <td class="text-success">₹ <?php echo number_format($total_price_inr, 2); ?></td>
                                            <td class="text-success"><?php echo date('d/M/Y g:i', strtotime($payment->created_at)); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <?php
            require_once('partials/_footer.php');
            ?>
        </div>
    </div>
    <!-- Argon Scripts -->
    <?php
    require_once('partials/_scripts.php');
    ?>
</body>

</html>
