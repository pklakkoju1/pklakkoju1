<?php
include 'config.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit();
}

$customers = [];
$not_found_message = "";

if (isset($_POST['generate_report'])) {
    $year = $_POST['year'];
    $search_type = $_POST['search_type'];
    $search_value = $_POST['search_value'];

    $sql = "SELECT c.customer_id, c.customer_name, c.stb_id, c.phone_number, 
                   DATE_FORMAT(p.payment_date, '%Y-%m') AS month,
                   SUM(CASE WHEN p.payment_status = 'Paid' THEN p.amount ELSE 0 END) AS total_paid,
                   SUM(CASE WHEN p.payment_status = 'Not Paid' THEN p.amount ELSE 0 END) AS total_not_paid,
                   (SUM(CASE WHEN p.payment_status = 'Paid' THEN p.amount ELSE 0 END) - SUM(CASE WHEN p.payment_status = 'Not Paid' THEN p.amount ELSE 0 END)) AS balance
            FROM customers c
            LEFT JOIN payments p ON c.customer_id = p.customer_id
            WHERE YEAR(p.payment_date) = '$year'";

    if ($search_type == 'customer_name' && !empty($search_value)) {
        $sql .= " AND c.customer_name = '$search_value'";
    } elseif ($search_type == 'stb_id' && !empty($search_value)) {
        $sql .= " AND c.stb_id = '$search_value'";
    } elseif ($search_type == 'phone_number' && !empty($search_value)) {
        $sql .= " AND c.phone_number = '$search_value'";
    }

    $sql .= " GROUP BY c.customer_id, c.customer_name, c.stb_id, c.phone_number, month
              ORDER BY c.customer_id, month";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    } else {
        $not_found_message = "No payment details found for the selected criteria.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Report</title>
</head>
<body>
    <h2>Customer Report</h2>
    <form action="customer_report.php" method="post">
        Year: 
        <select name="year" required>
            <?php for ($y = date('Y'); $y >= 2000; $y--): ?>
                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
            <?php endfor; ?>
        </select>
        Search By: 
        <select name="search_type" required>
            <option value="">Select</option>
            <option value="customer_name">Customer Name</option>
            <option value="stb_id">STB ID</option>
            <option value="phone_number">Phone Number</option>
        </select>
        <input type="text" name="search_value" placeholder="Enter value" required>
        <input type="submit" name="generate_report" value="Generate Report">
    </form>

    <?php if ($not_found_message): ?>
        <p><?php echo $not_found_message; ?></p>
    <?php endif; ?>

    <?php if (!empty($customers)): ?>
        <table border="1">
            <tr>
                <th>Customer ID</th>
                <th>Name</th>
                <th>STB ID</th>
                <th>Phone Number</th>
                <th>Month</th>
                <th>Total Paid</th>
                <th>Total Not Paid</th>
                <th>Balance</th>
            </tr>
            <?php 
            $grand_total_paid = 0;
            $grand_total_not_paid = 0;
            $grand_total_balance = 0;
            foreach ($customers as $customer): 
                $grand_total_paid += $customer['total_paid'];
                $grand_total_not_paid += $customer['total_not_paid'];
                $grand_total_balance += $customer['balance'];
            ?>
                <tr>
                    <td><?php echo $customer['customer_id']; ?></td>
                    <td><?php echo $customer['customer_name']; ?></td>
                    <td><?php echo $customer['stb_id']; ?></td>
                    <td><?php echo $customer['phone_number']; ?></td>
                    <td><?php echo $customer['month']; ?></td>
                    <td><?php echo $customer['total_paid']; ?></td>
                    <td><?php echo $customer['total_not_paid']; ?></td>
                    <td><?php echo $customer['balance']; ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="5"><strong>Grand Total</strong></td>
                <td><strong><?php echo $grand_total_paid; ?></strong></td>
                <td><strong><?php echo $grand_total_not_paid; ?></strong></td>
                <td><strong><?php echo $grand_total_balance; ?></strong></td>
            </tr>
        </table>
    <?php endif; ?>
</body>
</html>
