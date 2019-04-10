<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 15px;
            color: #303030;
        }

        body {
            padding: 25px;
            text-align: center;
        }

        h1 {
            font-size: 1.4em;
            margin: 20px;
        }

        p {
            margin: 10px;
        }

        .g-recaptcha {
            margin: 10px auto;
            width: 304px;
        }

        button {
            padding: 10px;
            width: 100px;
            border-radius: 5px;
            background: #eee;
            cursor: pointer;
        }
    </style>
</head>
<body>
@yield('body')
</body>
</html>
