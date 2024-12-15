<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Cable Network Operator</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .exit-note {
            margin-bottom: 20px;
            font-size: 1.2em;
            color: #666;
        }
        .redirect-note {
            margin-top: 20px;
            font-size: 1em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Logout Successful</h2>
        <div class="exit-note">You have been logged out. Thank you for using the Cable Network Operator Portal.</div>
        <div class="redirect-note">You will be redirected to the login page shortly...</div>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = 'user_login.php';
        }, 3000); // Redirect after 3 seconds
    </script>
</body>
</html>
