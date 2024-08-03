<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>人狼</title>
  @vite(['resources/css/app.css', 'resources/js/play/app.js'])
  <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}">
  <style>
    body {
      background-color: #111;
      overflow-y: hidden;
    }

    ::-webkit-scrollbar {
      width: 10px;
    }

    ::-webkit-scrollbar-track {
      background: #fff;
      border: none;
      border-radius: 10px;
      box-shadow: inset 0 0 2px #777;
    }

    ::-webkit-scrollbar-thumb {
      background: #ccc;
      border-radius: 10px;
      box-shadow: none;
    }

    #main {
      margin: 0px;
      background-color: #333;
      text-align: center;
    }

    #app {
      max-width: 800px;
      margin: 0 auto;
    }
  </style>
</head>

<body>
  <div id="main">
    <div id="app"></div>
  </div>
</body>

</html>