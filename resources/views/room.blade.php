<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>部屋作成</title>
  @vite(['resources/css/app.css', 'resources/js/room/app.js'])
  <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}">
  <link href="//fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
  <link href="//cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
  <link href="//cdn.jsdelivr.net/npm/vuetify@3.x/dist/vuetify.min.css" rel="stylesheet">
</head>

<body>
  <div style="text-align:center; height:100vh;">
    <div id="app" style="max-width:800px; height:100%; margin: 0 auto;"></div>
  </div>
</body>

</html>