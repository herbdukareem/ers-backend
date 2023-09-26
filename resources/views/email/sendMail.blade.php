<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>OTP</title>
    <style>
        /* CSS Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
        }

        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }

        h4 {
            color: #333333;
            font-size: 18px;
            margin-bottom: 10px;
        }

        p {
            color: #666666;
            font-size: 16px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="<?= asset('image.png') ?>" alt="Logo" class="logo">
        <h4>Dear {{ $mailData['name'] }},</h4>
        <p>Below is your login OTP:</p>
        <p style="font-size: 24px; color: #007bff;">OTP: {{ $mailData['otp'] }}</p>
        <p>Expiry date and time: {{ $mailData['date'] }}</p>
    </div>
</body>
</html>
