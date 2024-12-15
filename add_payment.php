<?php
include 'config.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit();
}

$customers = [];
$not_found_message = "";
$balance = 0;
$customer_name = "";
$stb_id = "";
$address = "";
$pack_name = "";
$lco_id = "";

if (isset($_POST['search_customer'])) {
    $search_value = $_POST['search_value'];
    $search_type = $_POST['search_type'];

    $valid_columns = ['customer_name', 'stb_id', 'phone_number'];
    if (!in_array($search_type, $valid_columns)) {
        die("Invalid search type");
    }

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
        $not_found_message = "No customer found with the given $search_type.";
    }
}

if (isset($_POST['customer_id'])) {
    $customer_id = $_POST['customer_id'];

    $stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $customer_name = $customer['customer_name'];
    $stb_id = $customer['stb_id'];
    $address = $customer['address'];
    $balance = $customer['balance'];
    $pack_name = $customer['pack'];
    $lco_id = $customer['lco_id'];
}

if (isset($_POST['add_payment']) || isset($_POST['add_payment_generate_bill'])) {
    $customer_id = $_POST['customer_id'];
    $stb_id = $_POST['stb_id'];
    $address = $_POST['address'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $payment_status = $_POST['payment_status'];
    $payment_type = $_POST['payment_type'];
    $new_balance = $_POST['new_balance'];

    $stmt = $conn->prepare("INSERT INTO payments (customer_id, amount, payment_date, payment_status, payment_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $customer_id, $amount, $payment_date, $payment_status, $payment_type);
    if ($stmt->execute()) {
        $stmt = $conn->prepare("UPDATE customers SET balance = ? WHERE customer_id = ?");
        $stmt->bind_param("di", $new_balance, $customer_id);
        if ($stmt->execute()) {
            if (isset($_POST['add_payment_generate_bill'])) {
                echo "<script>window.open('generate_bill.php?customer_id=$customer_id&stb_id=' + encodeURIComponent('$stb_id') + '&amount=$amount&payment_date=$payment_date&payment_status=$payment_status&payment_type=$payment_type&pack_name=$pack_name&lco_id=$lco_id', '_blank');</script>";
            } else {
                echo "Payment added successfully. New Balance: $new_balance";
            }
        } else {
            echo "Error updating balance: " . $stmt->error;
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt = $conn->prepare("SELECT balance FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $balance = $customer['balance'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments CableBilling Suite</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
        }
        header, footer {
            background-color: #5706cc;
            color: #fff;
            text-align: center;
            padding: 1em 0;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        .dashboard-btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 20px 0;
            background-color: #5706cc;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .dashboard-btn:hover {
            background-color: #C70039;
        }
        form {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in-out;
        }
        form input[type="text"], form input[type="number"], form input[type="date"], form select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }
        form input[type="text"]:focus, form input[type="number"]:focus, form input[type="date"]:focus, form select:focus {
            border-color: #5706cc;
        }
        form input[type="submit"] {
            background-color: #5706cc;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        form input[type="submit"]:hover {
            background-color: #C70039;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <header>
        <h1><strong>GGBS ✦ CableBilling Suite®</strong></h1>
    </header>
    <div class="container">
        <a href="dashboard.php" class="dashboard-btn">Dashboard</a>
        <p>Collect Payments</p>
        <h2>Add Payment</h2>
        <form action="add_payment.php" method="post">
            <label for="search_type">Search Customer:</label>
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
            <form action="add_payment.php" method="post">
                <label for="customer_id">Select Customer:</label>
                <select name="customer_id" id="customer_id" required onchange="this.form.submit()">
                    <option value="">Select</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['customer_id']; ?>">
                            <?php echo $customer['customer_name'] . " - " . $customer['stb_id'] . " - " . $customer['phone_number']; ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>
            </form>
        <?php endif; ?>

        <?php if (isset($_POST['customer_id']) && !empty($_POST['customer_id'])): ?>
            <form action="add_payment.php" method="post">
                <input type="hidden" name="customer_id" value="<?php echo $_POST['customer_id']; ?>">
                <label for="customer_name">Customer Name:</label>
                <input type="text" name="customer_name" value="<?php echo $customer_name; ?>" readonly><br>
                <label for="stb_id">STB ID:</label>
                <input type="text" name="stb_id" value="<?php echo $stb_id; ?>" readonly><br>
                <label for="address">Address:</label>
                <input type="text" name="address" value="<?php echo $address; ?>" readonly><br>
                <label for="lco_id">LCO ID:</label>
                <input type="text" name="lco_id" value="<?php echo $lco_id; ?>" readonly><br>
                <label for="pack_name">Pack Name:</label>
                <input type="text" name="pack_name" value="<?php echo $pack_name; ?>" readonly><br>
                <label for="balance">Old Balance:</label>
                <input type="text" name="balance" value="<?php echo $balance; ?>" readonly><br>
                <label for="amount">Amount:</label>
                <input type="number" name="amount" required><br>
                <label for="new_balance">New Balance:</label>
                <input type="number" name="new_balance" required><br>
                <label for="payment_date">Payment Date:</label>
                <input type="datetime-local" id="payment_date" name="payment_date" required><br>
                <label for="payment_status">Payment Status:</label>
                <select name="payment_status" required>
                    <option value="Paid">Paid</option>
                    <option value="Not Paid">Not Paid</option>
                </select><br>
                <label for="payment_type">Payment Type:</label>
                <select name="payment_type" required>
                    <option value="Cash">Cash</option>
                    <option value="UPI">UPI</option>
                </select><br>
                <input type="submit" name="add_payment" value="Add Payment">
                <input type="submit" name="add_payment_generate_bill" value="Add Payment & Generate Bill">
            </form>
        <?php endif; ?>
    </div>
    <footer>
        <p>CableBilling Suite® - HUP™ © 2024. All rights reserved.</p>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.getElementById('payment_date').value = formattedDateTime;
        });
    </script>
</body>
</html>
