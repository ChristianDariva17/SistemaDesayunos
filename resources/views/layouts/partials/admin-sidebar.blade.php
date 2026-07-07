        {{-- ==========================================
            SIDEBAR
            ========================================== --}}
        <div id="sidebar-wrapper">
            {{-- Brand / Logo --}}
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <a href="{{ url('/') }}" class="sidebar-brand-text">
                    Caldos & Desayunos
                </a>
                <span class="sidebar-brand-subtext">Sistema de Gestión</span>
            </div>

            {{-- Navegación --}}
            <ul class="sidebar-nav">
                <li class="nav-heading">Menú Principal</li>

                {{-- Dashboard/Home --}}
                <li>
                    <a href="{{ url('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Panel de Control</span>
                    </a>
                </li>

                <li class="nav-heading">Gestión</li>

                {{-- Productos --}}
                <li>
                    <a href="{{ route('admin.productos.index') }}" class="{{ request()->is('productos*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i>
                        <span>Productos</span>
                        @if($stockBajo > 0)
                            <span class="badge badge-sidebar bg-warning">{{ $stockBajo }}</span>
                        @endif
                    </a>
                </li>

                {{-- Clientes --}}
                <li>
                    <a href="{{ route('admin.clientes.index') }}" class="{{ request()->is('clientes*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Clientes</span>
                    </a>
                </li>

                {{-- Pedidos --}}
                <li>
                    <a href="{{ route('admin.pedidos.index') }}" class="{{ request()->is('pedidos*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Pedidos</span>
                        @if($pedidosPendientes > 0)
                            <span class="badge badge-sidebar bg-danger">{{ $pedidosPendientes }}</span>
                        @endif
                    </a>
                </li>

                {{-- Empleados --}}
                <li>
                    <a href="{{ route('admin.empleados.index') }}" class="{{ request()->is('empleados*') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <span>Empleados</span>
                    </a>
                </li>

                <li class="nav-heading">Reportes</li>

                {{-- Reportes --}}
                <li>
                    <a href="{{ route('admin.reportes.index') }}" class="{{ request()->is('reportes*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Reportes</span>
                    </a>
                </li>

                <li class="nav-heading">Sistema</li>

                {{-- Configuración --}}
                @auth
                <li>
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
                @endauth
            </ul>

            {{-- User Info Footer --}}
            @auth
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="sidebar-user-info">
                        <h6>{{ Auth::user()->name }}</h6>
                        <small>{{ Auth::user()->email }}</small>
                    </div>
                </div>
            </div>
            @endauth
        </div>
