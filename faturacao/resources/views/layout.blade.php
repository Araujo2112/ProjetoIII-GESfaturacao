<!doctype html>
<html lang="pt">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'GesFaturação')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body>
    <div class="container-fluid">
      @if (request()->routeIs('login'))
        @yield('content')
      @else

      <div class="row" style="height: 100vh;">
        @include('partials.sidemenu')
        <div class="col-1 p-0 m-0" style="background: transparent;"></div>
        <div class="col-11 p-0 m-0" style="background: transparent;">
          @yield('content')
        </div>
      </div>
      @endif
    </div>

    {{-- Scripts específicos das páginas (gráficos, etc.) --}}
    @stack('scripts')

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>
</html>
