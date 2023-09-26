<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h1>Paramount Student Community Verify Email</h1>
    <p>Hi {{$mailData['name'] }}, please click on the link below to verify your email address.</p>
    <a href="{{ env('APP_URL').'/email_verify?verify=' .$mailData['token'] }}">Verify Email</a>
</body>
</html>