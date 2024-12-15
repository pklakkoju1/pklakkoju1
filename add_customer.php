<?php
include 'config.php';

if (isset($_POST['submit'])) {
    $name = $_POST['customer_name'];
    $address = $_POST['address'];
    $phone = $_POST['phone_number'];
    $stb_id = $_POST['stb_id'];
    $pack = $_POST['pack'];
    $connection_date = $_POST['connection_date'];
    $lco_id = $_POST['lco_id'];

    // Check for duplicate entry using stb_id only
    $check_sql = "SELECT * FROM customers WHERE stb_id = '$stb_id'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<div class='message'>Customer with this STB ID already exists.</div>";
    } else {
        $sql = "INSERT INTO customers (customer_name, address, phone_number, stb_id, pack, connection_date, lco_id)
                VALUES ('$name', '$address', '$phone', '$stb_id', '$pack', '$connection_date', '$lco_id')";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='message'>New customer added successfully</div>";
        } else {
            echo "<div class='message'>Error: " . $sql . "<br>" . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Customer - CableBilling Suite</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        header {
            background: #002366; /* Dark blue */
            color: #fff;
            padding: 20px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: #77aaff 3px solid;
            transition: background-color 0.3s;
        }
        header:hover {
            background-color: #003399; /* Slightly lighter dark blue */
        }
        header img {
            height: 78px; /* Increased by 20% from 65px */
            margin-right: 20px;
        }
        header nav {
            display: flex;
        }
        header nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        header nav ul li {
            margin-left: 20px;
        }
        header nav ul li a {
            color: #fff;
            text-decoration: none;
            text-transform: uppercase;
            font-size: 16px;
            transition: color 0.3s;
        }
        header nav ul li a:hover {
            color: #77aaff;
        }
        .content {
            padding: 20px;
            background: #fff;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .content:hover {
            transform: scale(1.02);
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="text"], input[type="date"] {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            background: #002366;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        input[type="submit"]:hover {
            background: #003399;
        }
        .message {
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
        }
        footer {
            background: #002366; /* Dark blue */
            color: #fff;
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        footer:hover {
            background-color: #003399; /* Slightly lighter dark blue */
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <img src="logo.png" alt="Cable Network Logo">
                <h1><strong>GGBS ✦ CableBilling Suite®</strong></h1>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="add_customer.php">Add Customer</a></li>
                    <li><a href="add_payment.php">Add Payment</a></li>
                    <li><a href="search_customer.php">Search Customer</a></li>
                    <li><a href="manage_payments.php">Manage Payments</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="customer_report.php">Customer Reports</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="content">
            <h2>Add Customer</h2>
            <form action="add_customer.php" method="post">
                <input type="text" name="customer_name" placeholder="Name" required>
                <input type="text" name="address" placeholder="Address" required>
                <input type="text" name="phone_number" placeholder="Phone Number" required>
                <input type="text" name="stb_id" placeholder="STB ID" required>
                <input type="text" name="pack" placeholder="Pack" required>
                <input type="date" name="connection_date" required>
                <input type="text" name="lco_id" placeholder="LCO ID" required>
                <input type="submit" name="submit" value="Add Customer">
            </form>

            <?php
            if (isset($_POST['submit'])) {
                $name = $_POST['customer_name'];
                $address = $_POST['address'];
                $phone = $_POST['phone_number'];
                $stb_id = $_POST['stb_id'];
                $pack = $_POST['pack'];
                $connection_date = $_POST['connection_date'];
                $lco_id = $_POST['lco_id'];

                // Check for duplicate entry using stb_id only
                $check_sql = "SELECT * FROM customers WHERE stb_id = '$stb_id'";
                $check_result = $conn->query($check_sql);

                if ($check_result->num_rows > 0) {
                    echo "<div class='message'>Customer with this STB ID already exists.</div>";
                } else {
                    $sql = "INSERT INTO customers (customer_name, address, phone_number, stb_id, pack, connection_date, lco_id)
                            VALUES ('$name', '$address', '$phone', '$stb_id', '$pack', '$connection_date', '$lco_id')";

                    if ($conn->query($sql) === TRUE) {
                        echo "<div class='message'>New customer added successfully</div>";
                    } else {
                        echo "<div class='message'>Error: " . $sql . "<br>" . $conn->error . "</div>";
                    }
                }
            }
            ?>
        </div>
    </div>

    <footer>
        <p>CableBilling Suite® - HUP™ © 2024. All rights reserved.</p>
    </footer>
</body>
</html>
