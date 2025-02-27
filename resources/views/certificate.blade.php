<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Participation</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            size: A5 landscape;
            margin: 0;
        }
        body {
            font-family: 'Georgia', serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .certificate-container {
            border: 20px solid #d4af37; /* Bingkai emas */
            padding: 50px;
            background-color: #fff;
            max-width: 800px;
            text-align: center;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .certificate-container::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border: 2px solid #d4af37;
            z-index: -1;
        }

        .title {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 1.2em;
            color: #7f8c8d;
            margin-bottom: 40px;
        }

        .event-details {
            font-size: 1.2em;
            color: #34495e;
            margin-bottom: 30px;
        }

        .event-details strong {
            color: #d4af37;
        }

        .signature {
            margin-top: 50px;
            font-family: 'Brush Script MT', cursive;
            font-size: 1.5em;
            color: #2c3e50;
        }

        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #7f8c8d;
        }
    </style>
</head>

<body>
    <div class="certificate-container">
        <div class="title">Certificate of Participation</div>
        <div class="subtitle">This certificate is proudly presented to</div>
        <div class="event-details">
            <strong>Participant:</strong> {{ $user->name }}<br>
            <strong>Event:</strong> {{ $event->name }}<br>
            <strong>Date:</strong> {{ $event->occasion_date }}
            <strong>No:</strong> {{ $certificateNumber }}
        </div>
        <div class="signature">
            <img src="https://via.placeholder.com/150x50.png?text=Signature" alt="Signature" style="margin-bottom: 10px;"><br>
            <strong>Authorized Signature</strong>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Intermedia. All rights reserved.
        </div>
    </div>
</body>

</html>
