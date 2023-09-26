<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Message Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        p {
            color: #666666;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .button {
            display: inline-block;
            background-color: #4caf50;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 3px;
        }

        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>New Message Notification</h2>

        <p>You have received a new message from  {{ $userType }} <strong>{{ $senderName }}</strong>:</p>
        <p>{!! $messageContent !!}</p>

        <p>Please log in to your account to view the message.</p>

        <p>Thank you!</p>

        <p>
            <a href="{{ url('/login') }}" class="button">Login</a>
        </p>
    </div>
</body>
</html>
