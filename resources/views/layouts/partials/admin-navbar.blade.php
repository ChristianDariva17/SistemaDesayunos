            {{-- Navbar Superior --}}
            <nav class="navbar navbar-expand-lg navbar-custom">
                <div class="container-fluid">
                    {{-- Toggle Sidebar Button --}}
                    <button id="sidebarToggle" type="button">
                        <i class="fas fa-bars"></i>
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
                            <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                                    {{ $pedidosPendientes ?? 0 }}
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Notificaciones</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('admin.pedidos.index') }}">
                                    <i class="fas fa-shopping-cart text-danger"></i>
                                    {{ $pedidosPendientes ?? 0 }} pedidos pendientes
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.productos.index') }}">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                    {{ $stockBajo ?? 0 }} productos con stock bajo
                                </a></li>
                            </ul>
                        </div>

                        {{-- User Dropdown --}}
                        @auth
                        <div class="dropdown user-dropdown">
                            <div class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><h6 class="dropdown-header">{{ Auth::user()->name }}</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Perfil</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Configuración</a></li>
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
