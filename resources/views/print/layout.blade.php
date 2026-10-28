<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #111; margin: 0; padding: 16px; background: #fff; }
        h1 { font-size: 16pt; margin: 0 0 2px; }
        .muted { color: #555; font-size: 9pt; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; border-bottom: 2px solid #111; padding-bottom: 8px; margin-bottom: 10px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px 16px; margin-bottom: 10px; }
        .grid div span { display: block; color: #555; font-size: 8.5pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #333; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #eee; font-size: 9pt; }
        td.blank { min-width: 70px; }
        td.box { text-align: center; width: 54px; }
        .write { border-bottom: 1px solid #333; display: inline-block; min-width: 180px; height: 18px; }
        .signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 28px; }
        .signatures div { border-top: 1px solid #333; padding-top: 4px; font-size: 9pt; }
        .toolbar { margin-bottom: 12px; }
        .toolbar button { padding: 6px 14px; font-size: 11pt; cursor: pointer; }
        @media print {
            .toolbar { display: none; }
            body { padding: 0; }
            @page { size: A4; margin: 12mm; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Print</button></div>
    @yield('content')
</body>
</html>
