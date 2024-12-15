<?php
include 'config.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit();
}

$customers = [];
$not_found_message = "";

if (isset($_POST['search_customer'])) {
    $search_value = $_POST['search_value'];
    $search_type = $_POST['search_type'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM customers WHERE $search_type LIKE ?");
    $search_value = "%$search_value%";
    $stmt->bind_param("s", $search_value);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    } else {
        $not_found_message = "No customers found with the given $search_type.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Customer - CableBilling Suite®</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header, footer {
            background: #38008a;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        .button {
            display: inline-block;
            color: #fff;
            background: #38008a;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
        }
        .button:hover {
            background: #555;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .form-container h2 {
            margin-top: 0;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
        }
        .form-container form input[type="text"],
        .form-container form select,
        .form-container form input[type="submit"] {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .dashboard-button {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>GGBS ✦ CableBilling Suite®</h1>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <a href="dashboard.php" class="button dashboard-button">Dashboard</a>
            <h2>Search Customer</h2>
            <p>Search customers with Name / STB ID / Phone Number</p>
            <form action="search_customer.php" method="post">
                <label for="search_type">Search By:</label>
                <select name="search_type" id="search_type">
                    <option value="customer_name">Name</option>
                    <option value="stb_id">STB ID</option>
                    <option value="phone_number">Phone Number</option>
                </select>
                <input type="text" name="search_value" required>
                <input type="submit" name="search_customer" value="Search">
            </form>

            <?php if ($not_found_message): ?>
                <p><?php echo $not_found_message; ?></p>
            <?php endif; ?>

            <?php if (!empty($customers)): ?>
                <table id="customersTable">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>LCO ID</th>
                            <th>Name</th>
                            <th>STB ID</th>
                            <th>Phone Number</th>
                            <th>Total Billed</th>
                            <th>Total Paid</th>
                            <th>Balance</th>
                            <th>Payment Date</th>
                            <th>Payment Amount</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <?php
                            $customer_id = $customer['customer_id'];
                            $sql = "SELECT SUM(amount) AS total_billed, SUM(CASE WHEN payment_status = 'Paid' THEN amount ELSE 0 END) AS total_paid FROM payments WHERE customer_id = '$customer_id'";
                            $result = $conn->query($sql);
                            $payment_summary = $result->fetch_assoc();
                            $balance = $customer['balance'];

                            $sql = "SELECT payment_date, amount, payment_status FROM payments WHERE customer_id = '$customer_id'";
                            $result = $conn->query($sql);
                            $payment_details = [];
                            while ($row = $result->fetch_assoc()) {
                                $payment_details[] = $row;
                            }
                            ?>
                            <?php foreach ($payment_details as $index => $payment): ?>
                                <tr>
                                    <?php if ($index == 0): ?>
                                        <td rowspan="<?php echo count($payment_details); ?>"><?php echo $customer['customer_id']; ?></td>
                                        <td rowspan="<?php echo count($payment_details); ?>"><?php echo $customer['lco_id']; ?></td>
                                        <td rowspan="<?php echo count($payment_details); ?>"><?php echo $customer['customer_name']; ?></td>
                                        <td rowspan="<?php echo count($payment_details); ?>"><?php echo $customer['stb_id']; ?></td>
                                        <td rowspan="<?php echo count($payment_details); ?>"><?php echo $customer['phone_number']; ?></td>
                                        <td rowspan="<?php echo count($payment_details); ?>"><?php echo $payment_summary['total_billed']; ?></td>
                                        <td rowspan="<?php echo count($payment_details); ?>"><?php echo $payment_summary['total_paid']; ?></td>
                                        <td rowspan="<?php echo count($payment_details); ?>"><?php echo $balance; ?></td>
                                    <?php endif; ?>
                                    <td><?php echo $payment['payment_date']; ?></td>
                                    <td><?php echo $payment['amount']; ?></td>
                                    <td><?php echo $payment['payment_status']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>CableBilling Suite® - HUP™ © 2024. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#customersTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'excel', 'pdf'
                ],
                "footerCallback": function ( row, data, start, end, display ) {
                    var api = this.api(), data;
                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                    };
                    total = api
                        .column( 8 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                    pageTotal = api
                        .column( 8, { page: 'current'} )
                        .data()
                        .reduce( function (a, b) {
                            return intVal                        
                        .column( 8, { page: 'current'} )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                    $( api.column( 8 ).footer() ).html(
                        '₹'+pageTotal +' ( ₹'+ total +' total)'
                    );
                }
            });
        });
    </script>
</body>
</html>
