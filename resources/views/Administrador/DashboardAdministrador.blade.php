<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración | KRYMS</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard-admin.css') }}">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">K</span><span>KRYMS<small>ADMINISTRACIÓN</small></span></a>
            <p class="nav-label">GESTIÓN</p>
            <nav aria-label="Secciones administrativas">
                @foreach ([
                    'adhesiones' => 'Adhesiones',
                    'usuarios' => 'Usuarios',
                    'reportes' => 'Reportes',
                    'resenas' => 'Reseñas',
                ] as $clave => $titulo)
                    <a class="nav-link {{ $seccion === $clave ? 'active' : '' }}" href="{{ route('dashboard.admin', ['seccion' => $clave]) }}" @if ($seccion === $clave) aria-current="page" @endif>
                        <span>{{ $titulo }}</span>
                        <span class="nav-badge" aria-label="{{ $titulo }}: {{ $notificaciones[$clave] ?? 0 }} elementos">{{ $notificaciones[$clave] ?? 0 }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="sidebar-bottom">
                <div class="admin-identity"><span class="avatar">{{ strtoupper(substr(session('email', 'A'), 0, 1)) }}</span><span><strong>Administrador</strong><small>{{ session('email') }}</small></span></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout" type="submit">Cerrar sesión <span aria-hidden="true">↗</span></button>
                </form>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar"><span>Panel de control <span class="crumb">/ {{ ucfirst($seccion) }}</span></span><span class="live-indicator">Sistema operativo</span></header>
            <div class="page-wrap">
                <section class="page-heading">
                    <div>
                        <p class="eyebrow">KRYMS / ADMINISTRACIÓN</p>
                        <h1>{{ ['adhesiones' => 'Adhesiones', 'usuarios' => 'Usuarios', 'reportes' => 'Reportes', 'resenas' => 'Reseñas'][$seccion] }}</h1>
                        <p class="heading-copy">Supervisa las cuentas y el contenido de la plataforma.</p>
                    </div>
                    <span class="date-stamp">{{ now()->locale('es')->translatedFormat('d M Y') }}</span>
                </section>

                <section class="metrics" aria-label="Resumen">
                    <article class="metric"><span class="metric-label">Usuarios registrados</span><strong>{{ $totalUsuarios }}</strong><small>Clientes, locales y repartidores</small></article>
                    <article class="metric"><span class="metric-label">Adhesiones pendientes</span><strong>{{ $solicitudes->count() }}</strong><small>Locales y repartidores</small></article>
                    <article class="metric"><span class="metric-label">Reportes recibidos</span><strong>{{ $reportes->count() }}</strong><small>Reportes registrados</small></article>
                    <article class="metric"><span class="metric-label">Reseñas publicadas</span><strong>{{ $resenas->count() }}</strong><small>Reseñas disponibles para revisión</small></article>
                </section>

                <section class="data-panel">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">{{ ['adhesiones' => 'REVISIÓN DE CUENTAS', 'usuarios' => 'DIRECTORIO', 'reportes' => 'CENTRO DE INCIDENCIAS', 'resenas' => 'CONTENIDO DE LA COMUNIDAD'][$seccion] }}</p>
                            <h2>{{ ['adhesiones' => 'Solicitudes de adhesión', 'usuarios' => 'Cuentas registradas', 'reportes' => 'Reportes recibidos', 'resenas' => 'Reseñas registradas'][$seccion] }}</h2>
                        </div>
                        @if ($seccion === 'usuarios')
                            <form class="search-form" method="GET" action="{{ route('dashboard.admin') }}">
                                <input type="hidden" name="seccion" value="usuarios">
                                <label class="sr-only" for="user-search">Buscar usuario</label>
                                <input id="user-search" name="q" type="search" value="{{ $busqueda }}" placeholder="Nombre, correo o documento">
                                <button type="submit" aria-label="Buscar">Buscar</button>
                            </form>
                        @else
                            <span class="record-count">{{ ['adhesiones' => $solicitudes->count(), 'reportes' => $reportes->count(), 'resenas' => $resenas->count()][$seccion] }} registros</span>
                        @endif
                    </div>

                    @if (session('success'))
                        <div class="feedback success">{{ session('success') }}</div>
                    @endif
                    @if (session('warning'))
                        <div class="feedback warning">{{ session('warning') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="feedback warning">{{ $errors->first() }}</div>
                    @endif

                    @if ($seccion === 'adhesiones')
                        @if ($solicitudes->isEmpty())
                            <div class="empty-state"><span class="empty-icon">✓</span><strong>No hay adhesiones pendientes</strong><p>Las solicitudes nuevas de locales y repartidores aparecerán aquí.</p></div>
                        @else
                            <div class="table-scroll"><table><thead><tr><th>Solicitante</th><th>Tipo</th><th>Documento</th><th>Correo</th><th>Decisión</th></tr></thead><tbody>
                                @foreach ($solicitudes as $solicitud)
                                    <tr>
                                        <td>{{ $solicitud->nombre }}</td>
                                        <td><span class="type-label">{{ $solicitud->nombre_tipo }}</span></td>
                                        <td>{{ $solicitud->identificador }}</td>
                                        <td>{{ $solicitud->email }}</td>
                                        <td class="decision-cell">
                                            <form method="POST" action="{{ route('admin.adhesiones.update', [$solicitud->tipo, $solicitud->email]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="decision" value="aceptar">
                                                <button class="decision-accept" type="submit">Aceptar</button>
                                            </form>
                                            <details class="reject-details">
                                                <summary>Rechazar</summary>
                                                <form method="POST" action="{{ route('admin.adhesiones.update', [$solicitud->tipo, $solicitud->email]) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="decision" value="rechazar">
                                                    <label class="sr-only" for="motivo-{{ $loop->index }}">Motivo del rechazo</label>
                                                    <textarea id="motivo-{{ $loop->index }}" name="motivo" placeholder="Indica el motivo" required maxlength="1000"></textarea>
                                                    <button class="decision-reject" type="submit">Confirmar rechazo</button>
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody></table></div>
                        @endif
                    @elseif ($seccion === 'usuarios')
                        @if ($usuarios->isEmpty())
                            <div class="empty-state"><strong>{{ $busqueda ? 'No se encontraron usuarios' : 'Todavía no hay usuarios registrados' }}</strong><p>{{ $busqueda ? 'Prueba con otro nombre, correo o documento.' : 'Las cuentas registradas aparecerán en este listado.' }}</p></div>
                        @else
                            <div class="table-scroll"><table><thead><tr><th>Usuario</th><th>Tipo de cuenta</th><th>Identificador</th><th>Correo</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
                                @foreach ($usuarios as $usuario)
                                    <tr>
                                        <td class="user-cell"><span class="table-avatar">{{ strtoupper(substr($usuario->nombre, 0, 1)) }}</span>{{ $usuario->nombre }}</td>
                                        <td><span class="type-label">{{ $usuario->tipo }}</span></td>
                                        <td>{{ $usuario->identificador }}</td>
                                        <td>{{ $usuario->email }}</td>
                                        <td><span class="status {{ strtolower($usuario->estado) === 'activa' ? 'active' : 'suspended' }}">{{ $usuario->estado }}</span></td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.usuarios.estado', [$usuario->tipo_ruta, $usuario->email]) }}" onsubmit="return confirm('¿Confirmas el cambio de estado de esta cuenta?')">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="accion" value="{{ strtolower($usuario->estado) === 'activa' ? 'suspender' : 'activar' }}">
                                                <button class="account-action {{ strtolower($usuario->estado) === 'activa' ? 'suspend' : 'reactivate' }}" type="submit">{{ strtolower($usuario->estado) === 'activa' ? 'Suspender' : 'Reactivar' }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody></table></div>
                        @endif
                    @elseif ($seccion === 'reportes')
                        @if ($reportes->isEmpty())
                            <div class="empty-state"><strong>No hay reportes registrados</strong><p>Los reportes enviados aparecerán aquí para su revisión.</p></div>
                        @else
                            <div class="table-scroll"><table><thead><tr><th>Reporte</th><th>Cliente</th><th>Comercio</th><th>Detalle</th><th>Estado</th><th>Gestión</th></tr></thead><tbody>
                                @foreach ($reportes as $reporte)
                                    <tr>
                                        <td>#{{ $reporte->ID_Reporte }}</td>
                                        <td>{{ $reporte->CI_Cliente }}</td>
                                        <td>{{ $reporte->RUT_Comercio }}</td>
                                        <td class="detail-cell">{{ $reporte->Comentario ?: 'Sin comentario' }}</td>
                                        <td><span class="status {{ $reporte->Estado_Gestion === 'Resuelto' ? 'active' : 'pending' }}">{{ $reporte->Estado_Gestion ?? 'Pendiente' }}</span>@if ($reporte->Resolucion)<small class="resolution-note">{{ $reporte->Resolucion }}</small>@endif</td>
                                        <td class="management-cell">
                                            <form method="POST" action="{{ route('admin.reportes.update', $reporte->ID_Reporte) }}">
                                                @csrf
                                                @method('PATCH')
                                                <label class="sr-only" for="estado-reporte-{{ $reporte->ID_Reporte }}">Estado del reporte</label>
                                                <select id="estado-reporte-{{ $reporte->ID_Reporte }}" name="estado">
                                                    @foreach (['pendiente' => 'Pendiente', 'en_revision' => 'En revisión', 'informacion_solicitada' => 'Solicitar información', 'resuelto' => 'Resuelto'] as $valor => $etiqueta)
                                                        <option value="{{ $valor }}" @selected(($reporte->Estado_Gestion ?? 'Pendiente') === $etiqueta)>{{ $etiqueta }}</option>
                                                    @endforeach
                                                </select>
                                                <label class="sr-only" for="resolucion-{{ $reporte->ID_Reporte }}">Nota de gestión</label>
                                                <textarea id="resolucion-{{ $reporte->ID_Reporte }}" name="resolucion" placeholder="Nota o resolución">{{ $reporte->Resolucion }}</textarea>
                                                <button class="account-action" type="submit">Guardar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody></table></div>
                            <p class="table-note">Solicitar información o resolver un reporte requiere agregar una nota de gestión.</p>
                        @endif
                    @else
                        @if ($resenas->isEmpty())
                            <div class="empty-state"><strong>No hay reseñas registradas</strong><p>Las reseñas de clientes aparecerán aquí para su revisión.</p></div>
                        @else
                            <div class="table-scroll"><table><thead><tr><th>Reseña</th><th>Cliente</th><th>Destinatario</th><th>Puntuación</th><th>Comentario</th><th>Estado</th><th>Moderación</th></tr></thead><tbody>
                                @foreach ($resenas as $resena)
                                    <tr>
                                        <td>#{{ $resena->ID }}</td>
                                        <td>{{ $resena->CI_Cliente }}</td>
                                        <td>{{ $resena->RUT_Comercio ?: ($resena->CI_Repartidor ? 'Repartidor '.$resena->CI_Repartidor : 'Sin destinatario') }}</td>
                                        <td><span class="rating">{{ $resena->Puntuacion ?? '—' }} <span aria-hidden="true">★</span></span></td>
                                        <td class="detail-cell">{{ $resena->Comentario ?: 'Sin comentario' }}@if ($resena->Motivo_Retiro)<small class="resolution-note">{{ $resena->Motivo_Retiro }}</small>@endif</td>
                                        <td><span class="status {{ $resena->Estado_Moderacion === 'Retirada' ? 'suspended' : 'active' }}">{{ $resena->Estado_Moderacion ?? 'Visible' }}</span></td>
                                        <td class="management-cell">
                                            <form method="POST" action="{{ route('admin.resenas.moderate', $resena->ID) }}">
                                                @csrf
                                                @method('PATCH')
                                                @if ($resena->Estado_Moderacion === 'Retirada')
                                                    <input type="hidden" name="decision" value="mantener">
                                                    <button class="account-action" type="submit">Restaurar</button>
                                                @else
                                                    <label class="sr-only" for="motivo-resena-{{ $resena->ID }}">Motivo para retirar la reseña</label>
                                                    <textarea id="motivo-resena-{{ $resena->ID }}" name="motivo" placeholder="Motivo (si se retira)"></textarea>
                                                    <button class="account-action suspend" name="decision" value="retirar" type="submit">Retirar</button>
                                                    <button class="account-action" name="decision" value="mantener" type="submit">Mantener</button>
                                                @endif
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody></table></div>
                            <p class="table-note">Las reseñas retiradas dejan de estar disponibles y pueden restaurarse desde aquí.</p>
                        @endif
                    @endif
                </section>
                <footer class="page-footer">KRYMS <span>·</span> Gestión de plataforma</footer>
            </div>
        </main>
    </div>
</body>
</html>
