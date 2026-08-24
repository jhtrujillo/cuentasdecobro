<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuentas de Cobro - Dashboard</title>
    <!-- CSS Estilos Premium -->
    <link rel="stylesheet" href="assets/css/style.css?v=1.3">
</head>
<body>

    <div class="container">
        <!-- Encabezado Principal -->
        <header>
            <div>
                <h1>Generador de Cuentas de Cobro</h1>
                <p style="color: var(--text-secondary); margin-top: 0.25rem;">Gestión profesional de cobros con firma digital</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" id="btn-config-clientes">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Gestionar Clientes
                </button>
                <button class="btn btn-secondary" id="btn-config-emisor">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Configurar Mi Perfil
                </button>
                <button class="btn btn-primary" id="btn-nueva-cuenta">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Cuenta
                </button>
            </div>
        </header>

        <!-- Navegación Principal -->
        <div class="main-navigation">
            <button class="nav-btn active" id="nav-cuentas">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Cuentas de Cobro
            </button>
            <button class="nav-btn" id="nav-finanzas">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Control de Gastos e Ingresos
            </button>
        </div>

        <!-- SECCIÓN: CUENTAS DE COBRO -->
        <div id="section-cuentas">
            <!-- Tarjetas de Estadísticas -->
            <section class="stats-grid">
                <div class="card">
                    <div class="stat-title">Total Cobrado (Pagado)</div>
                    <div class="stat-value accent" id="stat-total-cobrado" style="color: #10b981;">$0</div>
                </div>
                <div class="card">
                    <div class="stat-title">Total Pendiente</div>
                    <div class="stat-value" id="stat-total-pendiente" style="color: #f59e0b;">$0</div>
                </div>
                <div class="card">
                    <div class="stat-title">Cuentas Emitidas</div>
                    <div class="stat-value" id="stat-cantidad-cuentas">0</div>
                </div>
            </section>

            <!-- Listado Principal -->
            <main class="table-card">
                <div class="table-header">
                    <div class="table-title">Historial de Cuentas de Cobro</div>
                    <div class="search-input-wrapper">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" class="search-input" id="table-search" placeholder="Buscar por cliente, N°, emisor...">
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>N° Cuenta</th>
                                <th>Cliente (Deudor)</th>
                                <th>Emisor (Acreedor)</th>
                                <th>Valor</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- El JS poblará dinámicamente las filas -->
                        </tbody>
                    </table>
                </div>
            </main>
        </div>

        <!-- SECCIÓN: CONTROL FINANCIERO -->
        <div id="section-finanzas" style="display: none;">
            <!-- Controles y Filtros -->
            <div class="finance-header-bar card" style="margin-bottom: 2rem; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <h2 style="font-family: var(--font-ui); font-size: 1.25rem; font-weight: 700; margin: 0; color: var(--text-primary);">Presupuesto Mensual</h2>
                    <div style="display: flex; gap: 0.5rem;">
                        <select id="filtro-mes" class="form-control" style="width: auto; padding: 0.4rem 0.8rem; font-size: 0.9rem; height: 38px;">
                            <option value="01">Enero</option>
                            <option value="02">Febrero</option>
                            <option value="03">Marzo</option>
                            <option value="04">Abril</option>
                            <option value="05">Mayo</option>
                            <option value="06">Junio</option>
                            <option value="07">Julio</option>
                            <option value="08">Agosto</option>
                            <option value="09">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                        <select id="filtro-anio" class="form-control" style="width: auto; padding: 0.4rem 0.8rem; font-size: 0.9rem; height: 38px;">
                            <option value="2025">2025</option>
                            <option value="2026" selected>2026</option>
                            <option value="2027">2027</option>
                            <option value="2028">2028</option>
                            <option value="2029">2029</option>
                            <option value="2030">2030</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button class="btn btn-secondary btn-sm" id="btn-exportar-excel" style="background-color: #10b981; border-color: #10b981; color: white;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2-8H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7l-4-4z" />
                        </svg>
                        Exportar Excel
                    </button>
                    <button class="btn btn-secondary btn-sm" id="btn-nuevo-ingreso">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Ingreso
                    </button>
                    <button class="btn btn-primary btn-sm" id="btn-nuevo-gasto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Gasto
                    </button>
                </div>
            </div>

            <!-- Tarjetas de Estadísticas Financieras -->
            <section class="stats-grid">
                <div class="card">
                    <div class="stat-title">Ingresos Totales (Mes)</div>
                    <div class="stat-value" id="stat-finanzas-ingresos" style="color: var(--success);">$0</div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;" id="breakdown-ingresos">Cuentas: $0 | Otros: $0</div>
                </div>
                <div class="card">
                    <div class="stat-title">Gastos Totales (Mes)</div>
                    <div class="stat-value" id="stat-finanzas-gastos" style="color: var(--danger);">$0</div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;" id="breakdown-gastos">0 transacciones</div>
                </div>
                <div class="card" id="card-balance">
                    <div class="stat-title">Balance Neto</div>
                    <div class="stat-value" id="stat-finanzas-balance">$0</div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;" id="breakdown-balance">Ahorro / Déficit</div>
                </div>
            </section>

            <!-- Distribución de Dos Columnas -->
            <div class="finanzas-layout">
                <!-- Columna Izquierda: Gastos -->
                <div class="table-card finanzas-col">
                    <div class="table-header">
                        <div class="table-title">Gastos del Mes</div>
                        <div class="header-search-filters">
                            <div class="search-input-wrapper">
                                <input type="text" class="search-input" id="search-gastos" placeholder="Buscar gasto..." style="padding-left: 1rem;">
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm filter-toggle-btn" id="btn-toggle-gasto-filters" title="Mostrar/Ocultar Filtros">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                <span class="hide-mobile">Filtros</span>
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Categoría</th>
                                    <th>Concepto</th>
                                    <th>Valor</th>
                                    <th>Estado</th>
                                    <th style="text-align: right;">Acciones</th>
                                </tr>
                                <tr class="filter-row">
                                    <th data-label="Fecha"><input type="text" id="filter-gasto-fecha" class="form-control filter-input" placeholder="Filtrar..."></th>
                                    <th data-label="Categoría">
                                        <select id="filter-gasto-categoria" class="form-control filter-input">
                                            <option value="">Todas</option>
                                            <option value="Arriendo / Vivienda">Arriendo / Vivienda</option>
                                            <option value="Servicios Públicos">Servicios Públicos</option>
                                            <option value="Alimentación / Supermercado">Alimentación / Supermercado</option>
                                            <option value="Transporte / Combustible">Transporte / Combustible</option>
                                            <option value="Salud">Salud</option>
                                            <option value="Educación">Educación</option>
                                            <option value="Entretenimiento / Ocio">Entretenimiento / Ocio</option>
                                            <option value="Seguros">Seguros</option>
                                            <option value="Impuestos">Impuestos</option>
                                            <option value="Pago Préstamos / Deudas">Pago Préstamos / Deudas</option>
                                            <option value="IndyOk">IndyOk</option>
                                            <option value="Otros Gastos">Otros Gastos</option>
                                        </select>
                                    </th>
                                    <th data-label="Concepto"><input type="text" id="filter-gasto-concepto" class="form-control filter-input" placeholder="Filtrar..."></th>
                                    <th data-label="Valor"><input type="text" id="filter-gasto-valor" class="form-control filter-input" placeholder="Filtrar..."></th>
                                    <th data-label="Estado">
                                        <select id="filter-gasto-ejecutado" class="form-control filter-input">
                                            <option value="">Todos</option>
                                            <option value="0">Pendiente</option>
                                            <option value="1">Ejecutado</option>
                                        </select>
                                    </th>
                                    <th style="text-align: right;" data-label="Limpiar">
                                        <button type="button" class="btn btn-secondary btn-sm" id="btn-clear-gasto-filters" title="Limpiar filtros" style="padding: 0.2rem 0.4rem; font-size: 0.75rem; min-height: auto; width: 100%;">
                                            Limpiar
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="table-gastos-body">
                                <!-- Poblado por JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Columna Derecha: Ingresos (Otros e Ingresos por cuentas) -->
                <div class="finanzas-col-right">
                    <!-- Otros Ingresos -->
                    <div class="table-card">
                        <div class="table-header">
                            <div class="table-title">Otros Ingresos (No Cuentas)</div>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Origen</th>
                                        <th>Concepto</th>
                                        <th>Valor</th>
                                        <th style="text-align: right;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="table-otros-ingresos-body">
                                    <!-- Poblado por JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Cuentas de Cobro Pagadas -->
                    <div class="table-card" style="margin-top: 1.5rem;">
                        <div class="table-header">
                            <div class="table-title" style="display: flex; justify-content: space-between; width: 100%; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                <span>Cuentas de Cobro Cobradas</span>
                                <span style="font-size: 0.8rem; font-weight: normal; color: var(--warning);" id="lbl-cuentas-pendientes">Pendientes en mes: $0</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody id="table-cuentas-mes-body">
                                    <!-- Poblado por JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: Crear / Editar Cuenta de Cobro -->
    <div class="modal" id="modal-cuenta">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-cuenta-title">Crear Cuenta de Cobro</h3>
                <button class="close-btn">&times;</button>
            </div>
            
            <div class="modal-body">
                <div class="modal-split-layout">
                    <!-- Columna Izquierda: Formulario -->
                    <form id="form-cuenta" autocomplete="off">
                        <input type="hidden" id="cuenta-id">
                        <input type="hidden" id="cuenta-valor-letras">

                        <h4 style="font-family: var(--font-ui); color: var(--text-primary); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Información del Documento</h4>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="cuenta-numero">Número de Cuenta #</label>
                                <input type="number" id="cuenta-numero" class="form-control" required min="1">
                            </div>
                            <div class="form-group">
                                <label for="cuenta-fecha">Fecha de Emisión</label>
                                <input type="date" id="cuenta-fecha" class="form-control" required>
                            </div>
                        </div>

                        <h4 style="font-family: var(--font-ui); color: var(--text-primary); margin: 1rem 0; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Datos del Cliente (Deudor)</h4>
                        
                        <div class="form-group full-width" style="margin-bottom: 1rem;">
                            <label for="select-deudor">Cargar Cliente Registrado (Autocompletar)</label>
                            <select id="select-deudor" class="form-control">
                                <option value="">-- Escribir manualmente o seleccionar cliente --</option>
                            </select>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="deudor-nombre">Nombre de la Empresa / Persona</label>
                                <input type="text" id="deudor-nombre" class="form-control" required placeholder="Ej: GRUAS Y TRANSPORTE DE COLOMBIA">
                            </div>
                            <div class="form-group">
                                <label for="deudor-nit">NIT / Cédula</label>
                                <input type="text" id="deudor-nit" class="form-control" required placeholder="Ej: 900667447-6">
                            </div>
                        </div>

                        <h4 style="font-family: var(--font-ui); color: var(--text-primary); margin: 1rem 0; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Datos de la Persona que Cobra (Acreedor)</h4>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="acreedor-nombre">Nombre Completo</label>
                                <input type="text" id="acreedor-nombre" class="form-control" required placeholder="Ej: JHON HENRY TRUJILLO MONTENEGRO">
                            </div>
                            <div class="form-group">
                                <label for="acreedor-documento">Número de Cédula (C.C.) / NIT</label>
                                <input type="text" id="acreedor-documento" class="form-control" required placeholder="Ej: 14.635.863">
                            </div>
                        </div>

                        <h4 style="font-family: var(--font-ui); color: var(--text-primary); margin: 1rem 0; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Detalles del Cobro</h4>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="cuenta-valor">Valor Numérico a Cobrar ($)</label>
                                <input type="number" id="cuenta-valor" class="form-control" required min="1" step="any" placeholder="Ej: 4741188">
                            </div>
                            <div class="form-group">
                                <label for="cuenta-pagado">Estado de Cobro</label>
                                <select id="cuenta-pagado" class="form-control">
                                    <option value="0">Pendiente</option>
                                    <option value="1">Cobrado (Pagado)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cuenta-rango-fechas">Rango de Fechas (Opcional)</label>
                            <input type="text" id="cuenta-rango-fechas" class="form-control" placeholder="Ej: del 01 al 15 de Mayo">
                        </div>

                        <div class="form-group">
                            <label for="cuenta-concepto">Concepto y Detalles de Pago</label>
                            <textarea id="cuenta-concepto" class="form-control" required rows="4" placeholder="Ej: Por acompañamiento vehicular. Por favor, realizar el pago a la cuenta de ahorros..."></textarea>
                        </div>

                        <!-- Sección de Firmas -->
                        <h4 style="font-family: var(--font-ui); color: var(--text-primary); margin: 1.5rem 0 0.5rem 0; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Firma del Acreedor</h4>
                        <div class="signature-box-container">
                            <div class="signature-tabs">
                                <button type="button" class="sig-tab active" id="tab-sig-draw">Dibujar Firma</button>
                                <button type="button" class="sig-tab" id="tab-sig-upload">Cargar Archivo</button>
                            </div>
                            <!-- Panel de dibujo -->
                            <div class="sig-content-pane active" id="pane-sig-draw">
                                <canvas id="signature-canvas" width="500" height="150"></canvas>
                                <div class="canvas-actions">
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">Use el ratón o panel táctil para dibujar la firma</span>
                                    <button type="button" class="btn btn-secondary btn-sm" id="clear-sig-btn" style="padding: 0.2rem 0.6rem; font-size: 0.75rem;">Limpiar</button>
                                </div>
                            </div>
                            <!-- Panel de carga -->
                            <div class="sig-content-pane" id="pane-sig-upload">
                                <label for="signature-file" style="margin-bottom: 0.5rem;">Seleccionar imagen PNG de su firma (Fondo transparente recomendado)</label>
                                <input type="file" id="signature-file" class="form-control" accept="image/*">
                                <img id="signature-upload-preview" class="signature-upload-preview" alt="Vista previa de firma cargada">
                            </div>
                        </div>
                    </form>

                    <!-- Columna Derecha: Vista Previa en Vivo -->
                    <div>
                        <h4 style="font-family: var(--font-ui); color: var(--text-primary); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Vista Previa del Documento</h4>
                        <div class="preview-container">
                            <div class="invoice-paper" id="invoice-preview-sheet" style="position: relative;">
                                <div id="prev-paid-stamp" style="display: none; position: absolute; top: 25px; right: 25px; border: 3px dashed #10b981; color: #10b981; font-size: 16px; font-weight: bold; text-transform: uppercase; padding: 4px 12px; border-radius: 5px; transform: rotate(-12deg); opacity: 0.85; user-select: none; letter-spacing: 2px; font-family: 'Courier New', Courier, monospace;">PAGADA</div>
                                
                                <div>
                                    <!-- Cabecera -->
                                    <div class="document-title">
                                        CUENTA DE COBRO # <span id="prev-num">1</span>
                                    </div>
                                    
                                    <!-- Deudor -->
                                    <div class="party-info">
                                        <div class="company" id="prev-deudor-nombre">GRUAS Y TRANSPORTE DE COLOMBIA</div>
                                        <div class="nit">Nit. <span id="prev-deudor-nit">900667447-6</span></div>
                                    </div>
                                    
                                    <!-- Debe a -->
                                    <div class="debe-a">
                                        DEBE A:<br>
                                        <span id="prev-acreedor-nombre-debe">JHON HENRY TRUJILLO MONTENEGRO</span><br>
                                        CC. <span id="prev-acreedor-doc-debe">14.635.863</span>
                                    </div>
                                    
                                    <!-- Suma de -->
                                    <div class="suma-seccion">
                                        <div class="suma-titulo">LA SUMA DE:</div>
                                        <div class="suma-texto" id="prev-letras">Cuatro millones setecientos cuarenta y un mil ciento ochenta y ocho pesos</div>
                                        <div class="suma-texto" id="prev-valor-num">($4.741.188)</div>
                                    </div>
                                    
                                    <!-- Concepto -->
                                    <div class="concepto-seccion">
                                        <div class="concepto-titulo">CONCEPTO:</div>
                                        <div class="concepto-texto" id="prev-concepto">Por acompañamiento vehicular. Por favor, realizar el pago al a cuenta de ahorros N°514-117196-38 de Bancolombia.</div>
                                    </div>
                                    
                                    <div class="concepto-seccion" id="prev-rango-seccion" style="display: none; margin-top: 15px;">
                                        <div class="concepto-titulo" style="font-size: 11px; font-weight: bold; margin-bottom: 5px; letter-spacing: 1px;">RANGO DE FECHAS:</div>
                                        <div class="concepto-texto" id="prev-rango-fechas" style="font-size: 13px;"></div>
                                    </div>
                                </div>
                                
                                <!-- Firma -->
                                <div class="firma-seccion">
                                    <img id="preview-firma-img" class="firma-imagen" alt="Firma digital" style="display: none;">
                                    <div class="firma-linea"></div>
                                    <div class="firma-nombre" id="prev-firma-nombre">JHON HENRY TRUJILLO MONTENEGRO</div>
                                    <div class="firma-identificacion">CC N° <span id="prev-firma-doc">14.635.863</span></div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
                <button type="submit" form="form-cuenta" class="btn btn-primary">Guardar Cuenta de Cobro</button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Configurar Emisor Predeterminado (Mi Perfil) -->
    <div class="modal" id="modal-emisor">
        <div class="modal-content issuer-modal-width">
            <div class="modal-header">
                <h3 class="modal-title">Mi Perfil (Emisor por Defecto)</h3>
                <button class="close-btn">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="form-emisor" autocomplete="off">
                    <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1.5rem;">Registre sus datos por defecto. Al crear una nueva cuenta de cobro, el sistema los cargará y configurará automáticamente el bloque del concepto bancario.</p>
                    
                    <div class="form-group">
                        <label for="emisor-nombre">Nombre Completo</label>
                        <input type="text" id="emisor-nombre" class="form-control" required placeholder="Ej: JHON HENRY TRUJILLO MONTENEGRO">
                    </div>
                    
                    <div class="form-group">
                        <label for="emisor-documento">Cédula (C.C.) o NIT</label>
                        <input type="text" id="emisor-documento" class="form-control" required placeholder="Ej: 14.635.863">
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="emisor-banco">Nombre de Banco</label>
                            <input type="text" id="emisor-banco" class="form-control" required placeholder="Ej: Bancolombia">
                        </div>
                        <div class="form-group">
                            <label for="emisor-tipo-cuenta">Tipo de Cuenta</label>
                            <select id="emisor-tipo-cuenta" class="form-control" required>
                                <option value="Ahorros">Ahorros</option>
                                <option value="Corriente">Corriente</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="emisor-numero-cuenta">Número de Cuenta Bancaria</label>
                        <input type="text" id="emisor-numero-cuenta" class="form-control" required placeholder="Ej: 514-117196-38">
                    </div>

                    <!-- Firma por defecto -->
                    <label style="margin-top: 1rem; display: block;">Firma Digital Predeterminada</label>
                    <div class="signature-box-container">
                        <div class="signature-tabs">
                            <button type="button" class="sig-tab active" id="e-tab-sig-draw">Dibujar Firma</button>
                            <button type="button" class="sig-tab" id="e-tab-sig-upload">Cargar Archivo</button>
                        </div>
                        <!-- Dibujar -->
                        <div class="sig-content-pane active" id="e-pane-sig-draw">
                            <canvas id="e-signature-canvas" width="500" height="150" style="background:#fff; border:1px dashed var(--text-muted); width:100%; height:120px;"></canvas>
                            <div class="canvas-actions">
                                <span style="font-size: 0.75rem; color: var(--text-muted);">Dibuje aquí</span>
                                <button type="button" class="btn btn-secondary btn-sm" id="e-clear-sig-btn" style="padding: 0.2rem 0.6rem; font-size: 0.75rem;">Limpiar</button>
                            </div>
                        </div>
                        <!-- Cargar -->
                        <div class="sig-content-pane" id="e-pane-sig-upload">
                            <input type="file" id="e-signature-file" class="form-control" accept="image/*">
                            <img id="e-signature-upload-preview" class="signature-upload-preview" alt="Firma predeterminada">
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
                <button type="submit" form="form-emisor" class="btn btn-primary">Guardar Configuración</button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: Gestionar Clientes (Empresas) -->
    <div class="modal" id="modal-clientes">
        <div class="modal-content issuer-modal-width">
            <div class="modal-header">
                <h3 class="modal-title">Gestionar Clientes</h3>
                <button class="close-btn">&times;</button>
            </div>
            
            <div class="modal-body">
                <!-- Formulario agregar/editar cliente -->
                <form id="form-cliente" autocomplete="off" style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <input type="hidden" id="cliente-id">
                    <h4 id="form-cliente-title" style="font-family: var(--font-ui); color: var(--text-primary); margin-bottom: 1rem; font-size: 0.95rem; font-weight: 600; text-transform: uppercase;">Registrar Nuevo Cliente</h4>
                    
                    <div class="form-group">
                        <label for="cliente-nombre">Nombre de la Empresa / Persona</label>
                        <input type="text" id="cliente-nombre" class="form-control" required placeholder="Ej: GRUAS Y TRANSPORTE DE COLOMBIA">
                    </div>
                    
                    <div class="form-group">
                        <label for="cliente-nit">NIT / Cédula / Documento</label>
                        <input type="text" id="cliente-nit" class="form-control" required placeholder="Ej: 900667447-6">
                    </div>

                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary btn-sm" id="btn-cancelar-cliente" style="display: none;">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btn-guardar-cliente">Guardar Cliente</button>
                    </div>
                </form>

                <!-- Listado de clientes -->
                <h4 style="font-family: var(--font-ui); color: var(--text-primary); margin-bottom: 1rem; font-size: 0.95rem; font-weight: 600; text-transform: uppercase;">Clientes Registrados</h4>
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>NIT</th>
                                <th style="text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="table-clientes-body">
                            <!-- Se poblará dinámicamente con JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-close-modal">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- MODAL 4: Agregar / Editar Gasto -->
    <div class="modal" id="modal-gasto">
        <div class="modal-content issuer-modal-width">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-gasto-title">Registrar Gasto</h3>
                <button class="close-btn">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="form-gasto" autocomplete="off">
                    <input type="hidden" id="gasto-id">
                    
                    <div class="form-group">
                        <label for="gasto-fecha">Fecha</label>
                        <input type="date" id="gasto-fecha" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="gasto-categoria">Categoría</label>
                        <select id="gasto-categoria" class="form-control" required>
                            <option value="">-- Seleccionar Categoría --</option>
                            <option value="Arriendo / Vivienda">Arriendo / Vivienda</option>
                            <option value="Servicios Públicos">Servicios Públicos (Luz, agua, internet...)</option>
                            <option value="Alimentación / Supermercado">Alimentación / Supermercado</option>
                            <option value="Transporte / Combustible">Transporte / Combustible</option>
                            <option value="Salud">Salud</option>
                            <option value="Educación">Educación</option>
                            <option value="Entretenimiento / Ocio">Entretenimiento / Ocio</option>
                            <option value="Seguros">Seguros</option>
                            <option value="Impuestos">Impuestos</option>
                            <option value="Pago Préstamos / Deudas">Pago Préstamos / Deudas</option>
                            <option value="IndyOk">IndyOk</option>
                            <option value="Otros Gastos">Otros Gastos</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="gasto-concepto">Concepto / Descripción</label>
                        <input type="text" id="gasto-concepto" class="form-control" required placeholder="Ej: Compra de papelería para oficina">
                    </div>

                    <div class="form-group">
                        <label for="gasto-valor">Monto / Valor ($)</label>
                        <input type="number" id="gasto-valor" class="form-control" required min="1" step="any" placeholder="Ej: 50000">
                    </div>

                    <div class="form-group">
                        <label for="gasto-ejecutado">Estado de Ejecución</label>
                        <select id="gasto-ejecutado" class="form-control">
                            <option value="0">Pendiente</option>
                            <option value="1">Ejecutado (Pagado)</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
                <button type="submit" form="form-gasto" class="btn btn-primary">Guardar Gasto</button>
            </div>
        </div>
    </div>

    <!-- MODAL 5: Agregar / Editar Otro Ingreso -->
    <div class="modal" id="modal-ingreso">
        <div class="modal-content issuer-modal-width">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-ingreso-title">Registrar Otro Ingreso</h3>
                <button class="close-btn">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="form-ingreso" autocomplete="off">
                    <input type="hidden" id="ingreso-id">
                    
                    <div class="form-group">
                        <label for="ingreso-fecha">Fecha</label>
                        <input type="date" id="ingreso-fecha" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="ingreso-categoria">Origen / Categoría</label>
                        <select id="ingreso-categoria" class="form-control" required>
                            <option value="">-- Seleccionar Origen --</option>
                            <option value="Salario / Honorarios">Salario / Honorarios</option>
                            <option value="Trabajo Independiente">Trabajo Independiente</option>
                            <option value="Ventas">Ventas</option>
                            <option value="Inversiones / Rendimientos">Inversiones / Rendimientos</option>
                            <option value="Regalos / Subsidios">Regalos / Subsidios</option>
                            <option value="IndyOk">IndyOk</option>
                            <option value="Otros Ingresos">Otros Ingresos</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ingreso-concepto">Concepto / Descripción</label>
                        <input type="text" id="ingreso-concepto" class="form-control" required placeholder="Ej: Pago de consultoría externa">
                    </div>

                    <div class="form-group">
                        <label for="ingreso-valor">Monto / Valor ($)</label>
                        <input type="number" id="ingreso-valor" class="form-control" required min="1" step="any" placeholder="Ej: 200000">
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
                <button type="submit" form="form-ingreso" class="btn btn-primary">Guardar Ingreso</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast">Operación realizada con éxito</div>

    <!-- JavaScript Lógica Premium -->
    <script src="assets/js/script.js?v=1.3"></script>
</body>
</html>
