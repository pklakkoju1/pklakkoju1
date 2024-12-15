<?php
include 'config.php';

if (isset($_POST['recover_password'])) {
    $username = $_POST['username'];
    $security_question = $_POST['security_question'];
    $security_answer = $_POST['security_answer'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Fetch the security question and answer from the database
    $stmt = $conn->prepare("SELECT security_answer FROM users WHERE username = ? AND security_question = ?");
    $stmt->bind_param("ss", $username, $security_question);
    $stmt->execute();
    $stmt->bind_result($hashed_answer);
    $stmt->fetch();
    $stmt->close();

    // Verify the security answer
    if (password_verify($security_answer, $hashed_answer)) {
        if ($new_password === $confirm_new_password) {
            $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update the password in the database
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->bind_param("ss", $new_hashed_password, $username);

            if ($stmt->execute()) {
                $success = "Password updated successfully";
            } else {
                $error = "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $error = "New passwords do not match.";
        }
    } else {
        $error = "Security answer is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Recovery - Cable Network Operator</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Add your CSS styles here */
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Recovery</h2>
        <?php if (isset($success)): ?>
            <div class="message"><?php echo $success; ?></div>
        <?php elseif (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="recover_password.php" method="post">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <select name="security_question" required>
                    <option value="">Select a security question</option>
                    <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                    <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                    <option value="What was the name of your elementary school?">What was the name of your elementary school?</option>
                    </select>
            </div>
            <div class="form-group">
                <input type="text" name="security_answer" placeholder="Answer" required>
            </div>
            <div class="form-group">
                <input type="password" name="new_password" placeholder="New Password" required>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_new_password" placeholder="Confirm New Password" required>
            </div>
            <button type="submit" name="recover_password" class="btn">Recover Password</button>
        </form>
    </div>
</body>
</html>
