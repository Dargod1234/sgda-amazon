<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion de Archivos</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <!-- Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/monolith.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">


    
          <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/monolith.min.css" />


    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script type="text/javascript" src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
 


    <!-- Alert Message-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Incluir el script de Pickr -->
    <!-- Modern or es5 bundle -->
    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.es5.min.js"></script>
    <!-- Incluir el tema deseado (classic, monolith o nano) -->

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>

                @guest
                    @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                    @endif

                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                @endguest
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar border-right">
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel d-flex flex-column">
                    <div class="image imagelogo">
                        <a href="{{ url('file-system') }}">
                            <img src="{{ asset('dist/img/logoIgamocol.png') }}" class="img" alt="User Image">
                        </a>
                    </div>
                    <div class="info infoname">
                        <p>{{ auth()->user()->name }}</p>
                         <p>Rol:</p><p>{{auth()->user()->role}}</p>
                    </div>
                </div>
                @php
                    $userRole = auth()->user()->role; // Obtener el rol del usuario
                @endphp
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ url('file-system') }}"
                                class="nav-link {{ request()->is('file-system') ? 'active' : '' }}">
                                <i class="fa-solid fa-code-branch"></i>
                                <p>Sistema de Archivos</p>
                            </a>
                        </li>

                        @if ($userRole === 'admin')
                            <li class="nav-item">
                                <a href="{{ url('/admin/users') }}"
                                    class="nav-link {{ request()->is('admin/users') ? 'active' : '' }}">
                                    <i class="bi bi-people"></i>
                                    <p>Usuarios</p>
                                </a>
                            </li>
                        @endif

                        @if ($userRole === 'admin')
                        
                            <li class="nav-item">
                                <a href="{{ route('admin.clients.index') }}"
                                    class="nav-link {{ request()->routeIs('clients.index') ? 'active' : '' }}">
                                    <i class="bi bi-people"></i>
                                    <p>Clientes</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('activities.index') }}"
                                    class="nav-link {{ request()->routeIs('activities.index') ? 'active' : '' }}">
                                    <i class="bi bi-bezier2"></i>
                                    <p>Historial Actividad</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('calendar.index') }}"
                                    class="nav-link {{ request()->routeIs('calendar.index') ? 'active' : '' }}">
                                    <i class="bi bi-calendar-week"></i>
                                    <p>Calendario</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('carpetas.obsoletas') }}"
                                    class="nav-link {{ request()->routeIs('carpetas.obsoletas') ? 'active' : '' }}">
                                    <i class="bi bi-archive"></i>
                                    <p>Archivador</p>
                                </a>
                            </li>
                        
                              <li class="nav-item nav-papelera">
                                <a href="{{ url('archivos/papelera') }}"
                                    class="nav-link {{ request()->is('archivos/papelera') ? 'active' : '' }}">
                                    <i class="fas fa-trash"></i>
                                    <p>Papelera</p>
                                </a>
                            </li>


                        @endif
                        
                      
                    </ul>
                </nav>

                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper h-auto">
            @if (($message = Session::get('mensaje')) && ($icon = Session::get('icono')))
                <script>
                    Swal.fire({
                        position: "top-end",
                        icon: "{{ $icon }}",
                        title: "{{ $message }}",
                        showConfirmButton: false,
                        timer: 3000
                    });
                </script>
            @endif
            <div class="container">
                @yield('content')
            </div>
        </div>
        <!-- /.content-wrapper -->

        <!-- Main Footer -->
        <footer class="main-footer">
            <!-- Default to the left -->
            <strong>Copyright &copy; 2025 <a href="https://dataigamocol.com/">IGAMOCOL S.A.S</a>.</strong> DATACOL SOFTWARE DE GESTION DOCUEMENTAL
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

    @yield('scripts') <!-- Asegúrate de que esta línea esté presente -->
</body>

</html>
