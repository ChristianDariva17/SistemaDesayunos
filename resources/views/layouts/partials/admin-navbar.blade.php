            {{-- Navbar Superior --}}
            <nav class="navbar navbar-expand-lg navbar-custom" aria-label="Barra superior de administración">
                <div class="container-fluid">
                    {{-- Toggle Sidebar Button --}}
                    <button id="sidebarToggle" type="button" aria-label="Alternar menú lateral" aria-controls="sidebar-wrapper">
                        <i class="fas fa-bars" aria-hidden="true"></i>
                    </button>

                    {{-- Breadcrumb --}}
                    <nav aria-label="breadcrumb" class="ms-3 d-none d-md-block">
                        <ol class="breadcrumb breadcrumb-custom mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </nav>

                    {{-- Right Side Navbar --}}
                    <div class="ms-auto d-flex align-items-center gap-3">
                        {{-- Notificaciones --}}
                        <div class="dropdown">
                            <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown" aria-label="Ver notificaciones">
                                <i class="fas fa-bell" aria-hidden="true"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;" aria-live="polite">
                                    {{ $pedidosPendientes ?? 0 }}
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Notificaciones</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('admin.pedidos.index') }}">
                                    <i class="fas fa-shopping-cart text-danger" aria-hidden="true"></i>
                                    {{ $pedidosPendientes ?? 0 }} pedidos pendientes
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.productos.index') }}">
                                    <i class="fas fa-exclamation-triangle text-warning" aria-hidden="true"></i>
                                    {{ $stockBajo ?? 0 }} productos con stock bajo
                                </a></li>
                            </ul>
                        </div>

                        {{-- User Dropdown --}}
                        @auth
                        <div class="dropdown user-dropdown">
                            <button type="button" class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir menú de usuario">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><h6 class="dropdown-header">{{ Auth::user()->name }}</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i> Perfil</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}"><i class="fas fa-cog me-2"></i> Configuración</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </div>
                        @endauth
                    </div>
                </div>
            </nav>
