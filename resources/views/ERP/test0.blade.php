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
            <hr>
            <ul>
                <li><a href="/test0/wo1">工單調度</a></li>
                <li><a href="/test0/ro1">收料單+發票(有bug)</a></li>
                <li><a href="/test0/po1">採購單紀錄(新)</a></li>
            </ul>
            <hr>
            <ul>
                <li><a href="/test0/logPoRcv">採購單→收料單(NTD)</a></li>
            </ul>
            <hr>
        </nav>
        <main>
            @yield('content')
        </main>
    </body>
</html>
