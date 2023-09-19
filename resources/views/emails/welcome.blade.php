<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to DACE!</title>
    <style>
        /* Material UI-inspired styles */
        body {
            font-family: 'Roboto', 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        h1 {
            margin-top: 0;
            color: #333;
        }

        p {
            margin-bottom: 20px;
            color: #555;
        }

        a {
            color: #00bcd4;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to DACE!</h1>

        <p>Thank you for joining our community. We're excited to have you on board!</p>

        <p>Here are your registration details:</p>

        <ul>
            <li><strong>Name:</strong> {{ $user->name }}</li>
            <li><strong>Email:</strong> {{ $user->email }}</li>
            <!-- Add any other user details you want to include -->
        </ul>

        <p>If you have any questions or need assistance, feel free to contact our support team.</p>

        <p>Thank you again for joining YourApp!</p>

        <p>Best regards,<br>
        DACE Team</p>
    </div>
</body>
</html>
