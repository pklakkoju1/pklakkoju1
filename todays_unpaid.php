<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

include('config.php');

$sql = "SELECT p.payment_id, p.amount, p.payment_date, p.payment_status, 
               c.customer_name as customer_name, c.phone_number as phone_number, c.stb_id , c.lco_id
        FROM payments p 
        JOIN customers c ON p.customer_id = c.customer_id
        WHERE p.payment_date = CURDATE() AND p.payment_status = 'not paid'";
$result = $conn->query($sql);

$payments = [];
$totalAmount = 0;
if ($result->num_rows > 0) {
    $payments = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($payments as $payment) {
        $totalAmount += $payment['amount'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Unpaid Payments</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .export-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
        }
        .export-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Today's Unpaid Payments</h2>
        <button id="export-btn" class="export-btn">Export to Excel</button>
        <table id="paymentsTable">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>LCO ID</th>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <th>STB ID</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo $payment['payment_id']; ?></td>
                        <td><?php echo $payment['lco_id']; ?></td>
                        <td><?php echo $payment['customer_name']; ?></td>
                        <td><?php echo $payment['phone_number']; ?></td>
                        <td><?php echo $payment['stb_id']; ?></td>
                        <td><?php echo $payment['amount']; ?></td>
                        <td><?php echo $payment['payment_date']; ?></td>
                        <td><?php echo $payment['payment_status']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No payments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right;"><strong>Total Amount:</strong></td>
                    <td><strong id="totalAmount"><?php echo $totalAmount; ?></strong></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#paymentsTable').DataTable({
                "footerCallback": function ( row, data, start, end, display ) {
                    var api = this.api(), data;
                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                    };
                    total = api
                        .column( 5 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                    pageTotal = api
                        .column( 5, { page: 'current'} )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                    $( api.column( 5 ).footer() ).html(
                        '₹'+pageTotal +' ( ₹'+ total +' total)'
                    );
                }
            });

            document.getElementById('export-btn').addEventListener('click', function() {
                var table = document.getElementById('paymentsTable');
                var wb = XLSX.utils.table_to_book(table, {sheet: "Sheet JS"});
                XLSX.writeFile(wb, 'todays_unpaid_payments.xlsx');
            });
        });
    </script>
</body>
</html>
