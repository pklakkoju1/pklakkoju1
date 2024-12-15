<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Monthly Payments Report</title>
</head>
<body>
    <h2>Monthly Payments Report</h2>
    <form action="reports.php" method="post">
        Select Month: <input type="month" name="report_month" required>
        <input type="submit" name="monthly_report" value="Generate Report">
    </form>

    <?php
    if (isset($_POST['monthly_report'])) {
        $report_month = $_POST['report_month'];

        $sql = "SELECT * FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$report_month'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table border='1'><tr><th>Payment ID</th><th>Customer ID</th><th>Payment Date</th><th>Amount</th><th>Payment Status</th><th>Payment Type</th></tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>".$row["payment_id"]."</td><td>".$row["customer_id"]."</td><td>".$row["payment_date"]."</td><td>".$row["amount"]."</td><td>".$row["payment_status"]."</td><td>".$row["payment_type"]."</td></tr>";
            }
            echo "</table>";
        } else {
            echo "No payments found for the selected month.";
        }
    }
    ?>
</body>
</html>
