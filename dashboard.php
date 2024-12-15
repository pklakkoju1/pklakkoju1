<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Include the database configuration file
include('config.php');

// Fetch total customers
$total_customers_sql = "SELECT COUNT(*) as total_customers FROM customers";
$total_customers_result = $conn->query($total_customers_sql);
$total_customers = $total_customers_result->fetch_assoc()['total_customers'];

// Fetch today's paid payments
$todays_paid_sql = "SELECT COUNT(*) as todays_paid FROM payments WHERE payment_date = CURDATE() AND payment_status = 'paid'";
$todays_paid_result = $conn->query($todays_paid_sql);
$todays_paid = $todays_paid_result->fetch_assoc()['todays_paid'];

// Fetch today's Unpaid payments
$todays_Unpaid_sql = "SELECT COUNT(*) as todays_Unpaid FROM payments WHERE payment_date = CURDATE() AND payment_status = 'Not paid'";
$todays_Unpaid_result = $conn->query($todays_Unpaid_sql);
$todays_Unpaid = $todays_Unpaid_result->fetch_assoc()['todays_Unpaid'];

// Fetch monthly payments count
$monthly_payments_sql = "SELECT COUNT(*) as monthly_payments FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())";
$monthly_payments_result = $conn->query($monthly_payments_sql);
$monthly_payments = $monthly_payments_result->fetch_assoc()['monthly_payments'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CableBilling Suite - HUP™</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        header {
            background: #002366;
            color: #fff;
            padding: 20px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: #77aaff 3px solid;
            transition: background-color 0.3s;
        }
        header:hover {
            background-color: #003399;
        }
    /* Mobile-first styles */
        @media (max-width: 768px) {
         header nav ul {
        flex-direction: column;
        }
        header nav ul li {
        margin-left: 0;
        margin-bottom: 10px;
         }
    .dashboard {
        flex-direction: column;
        }
    .dashboard div {
        width: 100%;
    }
    }

        header img {
            height: 78px;
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
        .dashboard {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        .dashboard div {
            background: #77aaff;
            color: #fff;
            padding: 20px;
            border-radius: 5px;
            width: 30%;
            text-align: center;
        }
        .dashboard div a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }
        footer {
            background: #002366;
            color: #fff;
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        footer:hover {
            background-color: #003399;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <img src="logo.png" alt="Cable Network Logo">
                <h1><strong>Girish Geethik Cable TV and Broadband Billing System</strong></h1>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="add_customer.php">Add Customer</a></li>
                    <li><a href="add_payment.php">Add Payment</a></li>
                    <li><a href="search_customer.php">Search Customer</a></li>
                    <!-- <li><a href="manage_payments.php">Mng Payment</a></li> -->
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="update_customers.php">Modify Customers</a></li>
                    <li><a href="user_logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="content">
            <h2>Welcome to Cable Billing System! <?php echo $_SESSION['username']; ?></h2>
            <p>We’re thrilled to have you onboard. Our billing system is designed to streamline your cable network management with ease and efficiency.</p>
            <p>Use the navigation links above to manage customers, payments, and generate reports.</p>
        </div>
    </div>

    <div class="container">
        <div class="content">
            <h2>Dashboard</h2>
            <div class="dashboard">
                <div>
                    <h3>Total Customers</h3>
                    <p><?php echo $total_customers; ?></p>
                    <a href="total_customers.php" target="_blank">View Details</a>
                </div>
                <div>
                    <h3>Today's Paid Payments</h3>
                    <p><?php echo $todays_paid; ?></p>
                    <a href="todays_paid.php" target="_blank">View Details</a>
                </div>
                <div>
                    <h3>Today's Not Paid Payments</h3>
                    <p><?php echo $todays_Unpaid; ?></p>
                    <a href="todays_Unpaid.php" target="_blank">View Details</a>
                </div>
                <div>
                    <h3>Monthly Payments Count</h3>
                    <p><?php echo $monthly_payments; ?></p>
                    <a href="monthly_payments.php" target="_blank">View Details</a>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
            <h2>Key Features:</h2>
                <ul>
                    <li>Effortless Bill Payments: Quickly and securely manage your customers bills.</li>
                    <li>Comprehensive Account Management: Update your personal information and preferences with ease.</li>
                    <li>Detailed Billing History: Access and review your past bills and payment records.</li>
                    <li>Customizable Reports: Generate detailed reports to keep track of your finances and customer data.</li>
                </ul>
        <p>This application was meticulously crafted by <strong>Mr.Pavan Kumar Lakkoju</strong> to ensure a seamless experience for you. We hope you find it as efficient and user-friendly as we intended.If you need any assistance contact Pavan. Thank you for choosing</p>
        </div>

    <footer>
        <p><strong>CableBilling Suite® - HUP™ © 2024. All rights reserved.</strong></p>
    </footer>
</body>
</html>
