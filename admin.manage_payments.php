<?php
include 'config.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit();
}

function deletePayment($conn, $payment_id) {
    $stmt = $conn->prepare("DELETE FROM payments WHERE payment_id = ?");
    $stmt->bind_param("i", $payment_id);
    if ($stmt->execute()) {
        echo "Payment deleted successfully";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

function updatePayment($conn, $payment_id, $amount, $payment_date, $payment_status, $payment_type, $balance) {
    $customer_id = null;
    $stmt = $conn->prepare("SELECT customer_id FROM payments WHERE payment_id = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $stmt->bind_result($customer_id);
    $stmt->fetch();
    $stmt->close();

    if ($customer_id) {
        $stmt = $conn->prepare("UPDATE payments SET amount = ?, payment_date = ?, payment_status = ?, payment_type = ?, balance = ? WHERE payment_id = ?");
        $stmt->bind_param("dsssdi", $amount, $payment_date, $payment_status, $payment_type, $balance, $payment_id);
        if ($stmt->execute()) {
            $stmt2 = $conn->prepare("UPDATE customers SET balance = ? WHERE customer_id = ?");
            $stmt2->bind_param("di", $balance, $customer_id);
            if ($stmt2->execute()) {
                echo "Payment and customer balance updated successfully";
            } else {
                echo "Error updating customer balance: " . $stmt2->error;
            }
            $stmt2->close();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error: Customer ID not found for payment ID $payment_id";
    }
}

if (isset($_POST['update_payment'])) {
    $payment_id = $_POST['payment_id'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $payment_status = $_POST['payment_status'];
    $payment_type = $_POST['payment_type'];
    $balance = $_POST['balance'];

    updatePayment($conn, $payment_id, $amount, $payment_date, $payment_status, $payment_type, $balance);
}

if (isset($_POST['delete_payment'])) {
    $payment_id = $_POST['payment_id'];
    deletePayment($conn, $payment_id);
}

$search = '';
$search_by = '';
$order_by = 'payment_id';
$order = 'ASC';
if (isset($_POST['search'])) {
    $search = $_POST['search'];
    $search_by = $_POST['search_by'];
    $order_by = $_POST['order_by'];
    $order = $_POST['order'];
    $stmt = $conn->prepare("SELECT payments.*, customers.customer_name, customers.phone_number, customers.balance FROM payments 
                            JOIN customers ON payments.customer_id = customers.customer_id 
                            WHERE $search_by LIKE ? ORDER BY $order_by $order");
    $search_param = "%$search%";
    $stmt->bind_param("s", $search_param);
} else {
    $stmt = $conn->prepare("SELECT payments.*, customers.customer_name, customers.phone_number, customers.balance FROM payments 
                            JOIN customers ON payments.customer_id = customers.customer_id ORDER BY $order_by $order");
}
$stmt->execute();
$result = $stmt->get_result();
$payments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Payments - CableBilling Suite® - HUP™</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            color: #333;
            margin: 0;
            padding: 0;
        }
        header, footer {
            background-color: #00509e;
            color: white;
            text-align: center;
            padding: 10px 0;
        }
        header h1, footer p {
            margin: 0;
        }
        .container {
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #00509e;
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #00509e;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        form {
            display: inline;
        }
        input[type="submit"], input[type="number"], input[type="date"], select, input[type="text"] {
            padding: 8px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #00509e;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #003f7f;
        }
        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px;
        }
        .search-box {
            flex-grow: 1;
        }
        .dashboard-button {
            padding: 10px;
            background-color: #00509e;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .dashboard-button:hover {
            background-color: #003f7f;
        }
    </style>
</head>
<body>
    <header>
        <h1>GGBS ✦ CableBilling Suite®</h1>           
    </header>
                <p>Edit the wronge Payments, Delete Duplicate or Wrong payment entries, Use wisely and if it is needed</p>
    <div class="container">
        <div class="search-container">
            <div class="search-box">
                <form action="manage_payments.php" method="post">
                    <select name="search_by">
                        <option value="customers.customer_name">Customer Name</option>
                        <option value="payments.payment_id">Payment ID</option>
                        <option value="payments.customer_id">Customer ID</option>
                        <option value="customers.phone_number">Phone Number</option>
                    </select>
                    <input type="text" name="search" placeholder="Search" value="<?php echo $search; ?>">
                    <select name="order_by">
                        <option value="payment_id">Payment ID</option>
                        <option value="amount">Amount</option>
                        <option value="payment_date">Payment Date</option>
                        <option value="payment_status">Payment Status</option>
                    </select>
                    <select name="order">
                        <option value="ASC">Ascending</option>
                        <option value="DESC">Descending</option>
                    </select>
                    <input type="submit" value="Search">
                </form>
            </div>
            <a href="dashboard.php" class="dashboard-button">Go to Dashboard</a>
        </div>
        <h2>Manage Payments</h2>
        <table>
            <tr>
                <th>Payment ID</th>
                <th>Customer ID</th>
                <th>Customer Name</th>
                <th>Phone Number</th>
                <th>Amount</th>
                <th>Payment Date</th>
                <th>Payment Status</th>
                <th>Payment Type</th>
                <th>New Balance</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?php echo $payment['payment_id']; ?></td>
                    <td><?php echo $payment['customer_id']; ?></td>
                    <td><?php echo $payment['customer_name']; ?></td>
                    <td><?php echo $payment['phone_number']; ?></td>
                    <td><?php echo $payment['amount']; ?></td>
                    <td><?php echo $payment['payment_date']; ?></td>
                    <td><?php echo $payment['payment_status']; ?></td>
                    <td><?php echo $payment['payment_type']; ?></td>
                    <td><?php echo $payment['balance']; ?></td>
                    <td>
                        <form action="manage_payments.php" method="post">
                            <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                            <input type="submit" name="delete_payment" value="Delete">
                        </form>
                        <form action="manage_payments.php" method="post">
                            <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                            Amount: <input type="number" name="amount" value="<?php echo $payment['amount']; ?>" required>
                            Payment Date: <input type="date" name="payment_date" value="<?php echo $payment['payment_date']; ?>" required>
                            Payment Status: 
                            <select name="payment_status" required>
                                <option value="Paid" <?php if ($payment['payment_status'] == 'Paid') echo 'selected'; ?>>Paid</option>
                                <option value="Not Paid" <?php if ($payment['payment_status'] == 'Not Paid') echo 'selected'; ?>>Not Paid</option>
                            </select>
                            Payment Type: 
                            <select name="payment_type" required>
                                <option value="Cash" <?php if ($payment['payment_type'] == 'Cash') echo 'selected'; ?>>Cash</option>
                                <option value="UPI" <?php if ($payment['payment_type'] == 'UPI') echo 'selected'; ?>>UPI</option>
                            </select>
                            New Balance: <input type="number" name="balance" value="<?php echo $payment['balance']; ?>" required>
                            <input type="submit" name="update_payment" value="Update">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <footer>
        <p>CableBilling Suite® - HUP™ © 2024
        . All rights reserved.</p>
    </footer>
</body
</html>
