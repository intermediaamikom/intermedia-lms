<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .container {
            border: 10px solid #000;
            padding: 50px;
            margin: 50px auto;
            max-width: 800px;
        }
        .title {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .event-date, .user-name, .event-title {
            font-size: 1.5em;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">Certificate of Participation</div>
        <div class="event-date">Event Date: {{ $event->occasion_date }}</div>
        <div class="user-name">Participant: {{ $user->name }}</div>
        <div class="event-title">Event: {{ $event->name }}</div>
    </div>
</body>
</html>
