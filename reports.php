<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

include('config.php');

$payments = [];
$totalAmount = 0;

if (isset($_POST['daily_report'])) {
    $report_date = $_POST['report_date'];
    $sql = "SELECT p.payment_id, p.amount, p.payment_date, p.payment_status, 
                   c.customer_name as customer_name, c.phone_number as phone_number, c.stb_id, c.lco_id 
            FROM payments p 
            JOIN customers c ON p.customer_id = c.customer_id 
            WHERE p.payment_date = '$report_date'";
} elseif (isset($_POST['range_report'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $sql = "SELECT p.payment_id, p.amount, p.payment_date, p.payment_status, 
                   c.customer_name as customer_name, c.phone_number as phone_number, c.stb_id, c.lco_id 
            FROM payments p 
            JOIN customers c ON p.customer_id = c.customer_id 
            WHERE p.payment_date BETWEEN '$start_date' AND '$end_date'";
} else {
    $sql = "SELECT p.payment_id, p.amount, p.payment_date, p.payment_status, 
                   c.customer_name as customer_name, c.phone_number as phone_number, c.stb_id, c.lco_id 
            FROM payments p 
            JOIN customers c ON p.customer_id = c.customer_id 
            WHERE p.payment_date = CURDATE() AND p.payment_status = 'not paid'";
}

$result = $conn->query($sql);

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
    <title>Payments Reports - CableBilling Suite®</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1em 0;
            position: relative;
        }
        header .btn {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        h2 {
            color: #333;
            text-align: center;
            margin: 20px 0;
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
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
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
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
    <header>
        <h1>GGBS ✦ CableBilling Suite®</h1>
        <a href="dashboard.php" class="btn">Dashboard</a>
    </header>
    <div class="container">
        <h2>Daily Payments Report</h2>
        <form action="reports.php" method="post">
            Select Date: <input type="date" name="report_date" required>
            <input type="submit" name="daily_report" value="Generate Report" class="btn">
        </form>
        <h2>Payments Report by Date Range</h2>
        <form action="reports.php" method="post">
            Start Date: <input type="date" name="start_date" required>
            End Date: <input type="date" name="end_date" required>
            <input type="submit" name="range_report" value="Generate Report" class="btn">
        </form>

        <?php if (!empty($payments)): ?>
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
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right;"><strong>Total Amount:</strong></td>
                        <td><strong id="totalAmount"><?php echo $totalAmount; ?></strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p>No payments found for the selected criteria.</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#paymentsTable').DataTable({
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
                XLSX.writeFile(wb, 'payments_report.xlsx');
            });
        });
    </script>
</body>
</html>
