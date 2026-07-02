<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sale Register PDF</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            background: #111827;
        }

        iframe {
            width: 100%;
            height: 100vh;
            border: 0;
            display: block;
        }
    </style>
</head>
<body>
    <iframe src="{{ $pdfUrl }}" title="Sale Register PDF"></iframe>
</body>
</html>
