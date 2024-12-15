<?php
include 'config.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit();
}

$customers = [];
$not_found_message = "";
$edit_message = "";

if (isset($_POST['search_customer'])) {
    $search_value = $_POST['search_value'];
    $search_type = $_POST['search_type'];

    // Validate search type to prevent SQL injection
    $allowed_search_types = ['customer_name', 'phone_number', 'stb_id', 'lco_id'];
    if (!in_array($search_type, $allowed_search_types)) {
        $not_found_message = "Invalid search type.";
    } else {
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
}

if (isset($_POST['edit_customer'])) {
    $customer_id = $_POST['customer_id'];
    $customer_name = $_POST['customer_name'];
    $phone_number = $_POST['phone_number'];
    $stb_id = $_POST['stb_id'];
    $address = $_POST['address'];
    $pack = $_POST['pack'];
    $connection_date = $_POST['connection_date'];
    $lco_id = $_POST['lco_id'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("UPDATE customers SET customer_name=?, phone_number=?, stb_id=?, address=?, pack=?, connection_date=?, lco_id=? WHERE customer_id=?");
    $stmt->bind_param("sssssssi", $customer_name, $phone_number, $stb_id, $address, $pack, $connection_date, $lco_id, $customer_id);

    if ($stmt->execute()) {
        $edit_message = "Customer details updated successfully.";
    } else {
        $edit_message = "Failed to update customer details.";
    }

    $stmt->close();
}

if (isset($_POST['delete_customer'])) {
    $customer_id = $_POST['customer_id'];

    // Delete related payments first
    $stmt = $conn->prepare("DELETE FROM payments WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $stmt->close();

    // Now delete the customer
    $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);

    if ($stmt->execute()) {
        $edit_message = "Customer deleted successfully.";
    } else {
        $edit_message = "Failed to delete customer.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Customers - CableBilling Suite®</title>
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
            width: 90%;
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
        .edit-form {
            display: flex;
            flex-direction: column;
        }
        .edit-form input[type="text"] {
            margin-bottom: 5px;
        }
    </style>
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this customer? This action cannot be undone.');
        }

        function showCustomerDetails() {
            var selectedCustomer = document.getElementById("customer_select").value;
            var customerDetails = document.getElementsByClassName("customer-details");
            for (var i = 0; i < customerDetails.length; i++) {
                customerDetails[i].style.display = "none";
            }
            document.getElementById(selectedCustomer).style.display = "block";
        }
    </script>
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
            <h2>Find and Update Customers</h2>
            <form action="update_customers.php" method="post">
                <label for="search_type">Search By:</label>
                <select name="search_type" id="search_type">
                    <option value="customer_name">Name</option>
                    <option value="stb_id">STB ID</option>
                    <option value="phone_number">Phone Number</option>
                    <option value="lco_id">LCO ID</option>
                </select>
                <input type="text" name="search_value" required>
                <input type="submit" name="search_customer" value="Search">
            </form>

            <?php if ($not_found_message): ?>
                <p><?php echo $not_found_message; ?></p>
            <?php endif; ?>

            <?php if (!empty($customers)): ?>
                <div class="form-container">
                    <h2>Select Customer to Edit</h2>
                    <label for="customer_select">Select Customer:</label>
                    <select id="customer_select" onchange="showCustomerDetails()">
                        <option value="">--Select Customer--</option>
                        <?php foreach ($customers as $index => $customer): ?>
                            <option value="customer_<?php echo $index; ?>"><?php echo $customer['customer_name']; ?> (ID: <?php echo $customer['customer_id']; ?>)</option>
                        <?php endforeach; ?>
                    </select>

                    <?php foreach ($customers as $index => $customer): ?>
                        <div id="customer_<?php echo $index; ?>" class="customer-details" style="display: none;">
                            <h2>Edit Customer Details</h2>
                            <form action="update_customers.php" method="post" class="edit-form">
                                <label for="customer_name">Name:</label>
                                <input type="text" name="customer_name" value="<?php echo $customer['customer_name']; ?>" required>

                                <label for="stb_id">STB ID:</label>
                                <input type="text" name="stb_id" value="<?php echo $customer['stb_id']; ?>" required>

                                <label for="phone_number">Phone Number:</label>
                                <input type="text" name="phone_number" value="<?php echo $customer['phone_number']; ?>" required>

                                <label for="address">Address:</label>
                                <input type="text" name="address" value="<?php echo $customer['address']; ?>" required>

                                <label for="pack">Pack:</label>
                                <input type="text" name="pack" value="<?php echo $customer['pack']; ?>" required>

                                <label for="connection_date">Connection Date:</label>
                                <input type="text" name="connection_date" value="<?php echo $customer['connection_date']; ?>" required>

                                <label for="lco_id">LCO ID:</label>
                                <input type="text" name="lco_id" value="<?php echo $customer['lco_id']; ?>" required>

                                <input type="hidden" name="customer_id" value="<?php echo $customer['customer_id']; ?>">
                                <input type="submit" name="edit_customer" value="Update" class="button">
                                <input type="submit" name="delete_customer" value="Delete" class="button" style="background-color: red;" onclick="return confirmDelete();">
                            </form>
                            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($edit_message): ?>
        <p><?php echo $edit_message; ?></p>
    <?php endif; ?>
</div>

<footer>
    <div class="container">
        <p>CableBilling Suite® - HUP™ © 2024. All rights reserved.</p>
    </div>
</footer>
</body>
</html>
