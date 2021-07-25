<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <style>
            html, body {
                font-family: 'Nunito', sans-serif;
                display: flex;
                height: 100%;
                width: 100%;
                margin: 0;
            }
            nav {
                width: 10%;
            }
            main {
                width: 90%;
                height: 100%;
            }
        </style>
    </head>
    <body>
        <nav>
            <ul>
                <li><a href="/test0/logTotalRo">採購單→收料單 統計(NTD)</a></li>
            </ul>
        </nav>
        <main>
            @yield('content')
        </main>
    </body>
</html>
