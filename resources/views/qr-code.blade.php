<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator</title>
</head>
<body>
    <form method="POST" action="{{ route('generate.qr') }}">
        @csrf
        <input type="text" name="number" placeholder="Enter number" required>
        <H1 style="font-size: 50px;">DAY 4 TESTING WAY TULOG TA ANI</H1>
        <button type="submit">DAY 4 TESTING WAY TULOG TA ANI</button>
    </form>

    @if(session('qr'))
        <img src="{{ session('qr') }}" alt="Generated QR Code">
    @endif
</body>
</html>
