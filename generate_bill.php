<?php
include 'config.php';

if (isset($_GET['customer_id'])) {
    $customer_id = $_GET['customer_id'];
    $amount = $_GET['amount'];
    $payment_date = $_GET['payment_date'];
    $payment_status = $_GET['payment_status'];
    $payment_type = $_GET['payment_type'];
    $pack_name = $_GET['pack_name'];

    // Fetch customer details
    $stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();

    // Fetch payment ID
    $stmt = $conn->prepare("SELECT payment_id FROM payments WHERE customer_id = ? ORDER BY payment_id DESC LIMIT 1");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    $bill_number = $payment['payment_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Bill</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Calibri, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
            position: relative;
        }
        .bill {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            page-break-inside: avoid;
            position: relative;
            z-index: 1;
        }
        .print-btn, .pdf-btn {
            margin: 10px 0;
        }
        @media print {
            .print-btn, .pdf-btn {
                display: none;
            }
        }
        header, footer {
            text-align: center;
            margin: 20px 0;
        }
        footer {
            margin-top: 50px;
            text-align: right;
        }
        .terms {
            margin-top: 20px;
            font-size: 0.9em;
        }
        .logo {
            position: absolute;
            top: 60px;
            left: 23px;
            width: 110px;
        }
        .header-box {
            border: 1px solid #000;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            position: relative;
        }
        .bill-header {
            text-align: center;
            position: relative;
        }
        .bill-date {
            position: absolute;
            top: 0;
            right: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header-box">
            <img src="logo.png" alt="Logo" class="logo">
            <h1>Girish Geethik Cable TV and Broadband Services</h1>
            <p><strong>Address:</strong> Av Complex, Nacharam Road, B.Gangaram, Sathupally, Khammam, Telangana - 507303.</p>
            <p>Contact Info: 08761-288363, 9666781740, 9705324132 | girishgeethiknetworks@gmail.com</p>
        </header>
        <div class="bill">
            <div class="bill-header">
                <h2><strong><u>CUSTOMER BILL</u></u></strong></h2>
                <p class="bill-date"><strong>Date:</strong> <?php echo $payment_date; ?></p>
            </div>
            <p><strong>Bill No:</strong> <?php echo $bill_number; ?></p>
            <p><strong>Name:</strong> <?php echo $customer['customer_name']; ?></p>
            <p><strong>Phone Number:</strong> <?php echo $customer['phone_number']; ?></p>
            <p><strong>Pack Name:</strong> <?php echo $pack_name; ?></p>
            <h3>Payment Details</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $amount; ?></td>
                        <td><?php echo $customer['balance']; ?></td>
                        <td><?php echo $payment_status; ?></td>
                        <td><?php echo $payment_type; ?></td>
                    </tr>
                </tbody>
            </table>
            <button class="btn btn-primary print-btn" onclick="window.print()">Print</button>
            <button class="btn btn-secondary pdf-btn" onclick="saveAsPDF()">Save as PDF</button>
        </div>
        <div class="terms">
            <h4>Terms and Conditions</h4>
            <p>1. Payment is due upon receipt.</p>
            <p>2. Late payments may incur additional charges.</p>
            <p>3. We accept payments via cash, UPI, and other specified methods.</p>
            <p>4. Services may be suspended or terminated if payments are not received by the due date.</p>
        </div>
            <div class="note">
                <p>Contact : 08761-288363, 9666781740, 9705324132 | girishgeethiknetworks@gmail.com. for Service and Recharge.</p>
            </div>
        <footer>
            <p>Authorized Sign</p>
            <p><strong>GG Broadband & Cable Network</strong></p>
        </footer>
    </div>
    <script>
        function saveAsPDF() {
            window.print();
        }
    </script>
</body>
</html>
