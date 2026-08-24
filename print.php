<?php
/**
 * Vista de Impresión Oficial de Cuenta de Cobro
 * Diseñada para coincidir exactamente con el formato físico de la imagen de referencia.
 */

require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id) {
    die("<h3>Error: Identificador de cuenta de cobro no especificado.</h3>");
}

try {
    $stmt = $pdo->prepare("SELECT * FROM cuentas WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $cuenta = $stmt->fetch();

    if (!$cuenta) {
        die("<h3>Error: Cuenta de cobro no encontrada en la base de datos.</h3>");
    }
} catch (PDOException $e) {
    die("<h3>Error de base de datos:</h3> " . $e->getMessage());
}

// Formatear valor en formato de pesos colombianos (Ej: 4741188 -> $4.741.188)
$valor_formateado = '$' . number_format($cuenta['valor'], 0, ',', '.');

// Función helper para formatear documento del deudor (Nit o CC)
function formatDocumento($doc) {
    $doc = trim($doc);
    $docLower = strtolower($doc);
    if (empty($doc)) return '';
    
    // Si ya empieza con algún prefijo, lo dejamos tal cual
    if (strpos($docLower, 'nit') === 0 || strpos($docLower, 'cc') === 0 || strpos($docLower, 'c.c') === 0 || strpos($docLower, 'cedula') === 0 || strpos($docLower, 'c.e') === 0) {
        return $doc;
    }
    
    // Si contiene guión, asumimos que es NIT
    if (strpos($doc, '-') !== false) {
        return 'Nit. ' . $doc;
    }
    
    // Si es sólo numérico, asumimos Cédula de Ciudadanía
    return 'C.C. ' . $doc;
}

// Limpiar concepto para evitar espacios en blanco (forzar máximo 1 salto de línea)
$concepto_limpio = htmlspecialchars($cuenta['concepto']);
$concepto_limpio = preg_replace('/(\r\n|\n|\r)+/', "\n", $concepto_limpio);
$concepto_limpio = nl2br($concepto_limpio);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta de Cobro # <?php echo htmlspecialchars($cuenta['numero_cuenta']); ?></title>
    <!-- Librería html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        /* Estilos generales de visualización en pantalla */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #525659;
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            color: #000000;
        }

        /* Barra de control superior (sólo pantalla) */
        .control-bar {
            width: 100%;
            max-width: 800px;
            background: #202124;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        .control-bar span {
            color: #ffffff;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-print {
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s;
        }

        .btn-print:hover {
            background-color: #1d4ed8;
        }

        /* Contenedor de la hoja (Proporciones de papel carta) */
        .paper-sheet {
            background-color: #ffffff;
            width: 816px; /* Ancho estándar carta en px a 96 DPI */
            min-height: 1056px; /* Alto estándar carta en px a 96 DPI */
            padding: 60px 70px; /* Márgenes amplios de impresión */
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        /* Formato e Imprenta del Documento (Igual a la imagen del cliente) */
        .document-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 55px;
        }

        .party-debtor {
            text-align: center;
            margin-bottom: 40px;
            font-size: 14px;
            line-height: 1.6;
        }

        .party-debtor .name {
            font-weight: bold;
            text-transform: uppercase;
        }

        .party-debtor .nit {
            font-weight: normal;
        }

        .debe-a-section {
            text-align: center;
            margin-bottom: 40px;
            font-size: 14px;
            line-height: 1.5;
        }

        .debe-a-section .label {
            font-weight: normal;
            margin-bottom: 10px;
        }

        .debe-a-section .name {
            font-weight: bold;
            text-transform: uppercase;
        }

        .debe-a-section .id {
            font-weight: bold;
            text-transform: uppercase;
        }

        .suma-section {
            text-align: center;
            margin-bottom: 50px;
            font-size: 14px;
            line-height: 1.6;
        }

        .suma-section .label {
            font-weight: normal;
            letter-spacing: 1px;
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        .suma-section .value-text {
            font-weight: bold;
            padding: 0 20px;
            display: block;
        }

        .suma-section .value-number {
            font-weight: bold;
            margin-top: 5px;
            display: block;
        }

        .concepto-section {
            text-align: center;
            margin-bottom: 50px;
            font-size: 14px;
            line-height: 1.6;
        }

        .concepto-section .label {
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .concepto-section .body-text {
            text-align: justify;
            font-size: 14px;
            line-height: 1.8;
            white-space: pre-wrap;
            padding: 0 10px;
        }

        /* Sección de Firma en la parte inferior izquierda */
        .firma-container {
            margin-top: auto;
            text-align: left;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding-top: 40px;
        }

        .firma-img {
            max-height: 90px;
            max-width: 280px;
            object-fit: contain;
            margin-bottom: -5px;
        }

        .firma-line {
            border-top: 1px solid #000000;
            width: 320px;
            margin-bottom: 8px;
        }

        .firma-name {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
        }

        .firma-doc {
            font-size: 13px;
        }

        /* Estilos de Impresión */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
                margin: 0;
            }

            .control-bar {
                display: none !important;
            }

            .paper-sheet {
                width: 100% !important;
                height: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                padding: 50px 45px 30px 45px !important;
                page-break-after: avoid;
                page-break-before: avoid;
            }

            /* Forzar fondos en impresión en caso de ser necesario */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* Quitar sombra y bordes en PDF generado por html2pdf */
        .html2pdf-container .paper-sheet {
            box-shadow: none !important;
            border: none !important;
            margin: 0 !important;
            width: 100% !important;
        }

        .paid-stamp {
            position: absolute;
            top: 45px;
            right: 45px;
            border: 3px dashed #10b981;
            color: #10b981;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 6px;
            transform: rotate(-12deg);
            opacity: 0.85;
            user-select: none;
            letter-spacing: 2px;
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
</head>
<body>

    <!-- Barra de control en pantalla para el usuario -->
    <div class="control-bar">
        <span>Vista previa - Cuenta de Cobro # <?php echo htmlspecialchars($cuenta['numero_cuenta']); ?></span>
        <div style="display: flex; gap: 10px;">
            <button class="btn-print btn-download-pdf" onclick="descargarPDF();" style="background-color: #10b981;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="margin-right: 4px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2-8H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7l-4-4z" />
                </svg>
                Descargar PDF
            </button>
            <button class="btn-print" onclick="window.print();">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                </svg>
                Imprimir Cuenta
            </button>
        </div>
    </div>

    <!-- Hoja física de la Cuenta de Cobro -->
    <div class="paper-sheet">
        <?php if (isset($cuenta['pagado']) && $cuenta['pagado'] == 1): ?>
            <div class="paid-stamp">PAGADA</div>
        <?php endif; ?>
        
        <div>
            <!-- Título Principal -->
            <div class="document-title">
                CUENTA DE COBRO # <?php echo htmlspecialchars($cuenta['numero_cuenta']); ?>
            </div>
            
            <div style="text-align: center; margin-top: 5px; margin-bottom: 25px; font-size: 14px;">
                <strong>Fecha de Creación:</strong> <?php echo date('d/m/Y', strtotime($cuenta['created_at'])); ?>
            </div>

            <!-- Deudor (Cliente que debe pagar) -->
            <div class="party-debtor">
                <div class="name"><?php echo htmlspecialchars($cuenta['deudor_nombre']); ?></div>
                <div class="nit"><?php echo htmlspecialchars(formatDocumento($cuenta['deudor_nit'])); ?></div>
            </div>

            <!-- Acreedor (Quien cobra) -->
            <div class="debe-a-section">
                <div class="label">DEBE A:</div>
                <div class="name"><?php echo htmlspecialchars($cuenta['acreedor_nombre']); ?></div>
                <div class="id">CC. <?php echo htmlspecialchars($cuenta['acreedor_documento']); ?></div>
            </div>

            <!-- Suma de Dinero (Letras y Números) -->
            <div class="suma-section">
                <div class="label">LA SUMA DE:</div>
                <div class="value-text"><?php echo htmlspecialchars($cuenta['valor_letras']); ?></div>
                <div class="value-number">(<?php echo htmlspecialchars($valor_formateado); ?>)</div>
            </div>

            <!-- Concepto y Datos de Pago -->
            <div class="concepto-section">
                <div class="label">CONCEPTO:</div>
                <div class="body-text"><?php echo $concepto_limpio; ?></div>
            </div>

            <?php if (!empty($cuenta['rango_fechas'])): ?>
            <div class="concepto-section" style="margin-top: 15px;">
                <div class="label">RANGO DE FECHAS:</div>
                <div class="body-text"><?php echo htmlspecialchars($cuenta['rango_fechas']); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Firma Digital en la parte inferior izquierda -->
        <div class="firma-container">
            <?php if (!empty($cuenta['firma_base64'])): ?>
                <img class="firma-img" src="<?php echo $cuenta['firma_base64']; ?>" alt="Firma">
            <?php else: ?>
                <!-- Espacio de firma si no hay cargada -->
                <div style="height: 50px;"></div>
            <?php endif; ?>
            
            <div class="firma-line"></div>
            <div class="firma-name"><?php echo htmlspecialchars($cuenta['acreedor_nombre']); ?></div>
            <div class="firma-doc">CC N° <?php echo htmlspecialchars($cuenta['acreedor_documento']); ?></div>
        </div>

    </div>

    <script>
        function descargarPDF() {
            const elemento = document.querySelector('.paper-sheet');
            const opt = {
                margin:       0,
                filename:     'cuenta_cobro_' + <?php echo json_encode($cuenta['numero_cuenta']); ?> + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            
            const btn = document.querySelector('.btn-download-pdf');
            let originalText = '';
            if (btn) {
                originalText = btn.innerHTML;
                btn.innerHTML = `
                    <svg class="animate-spin" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="margin-right: 4px; animation: spin 1s linear infinite;">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle>
                        <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" style="opacity: 0.75;"></path>
                    </svg>
                    Generando...
                `;
                btn.disabled = true;
            }
            
            html2pdf().set(opt).from(elemento).save().then(() => {
                if (btn) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
                
                // Si se especificó ?download=1, cerrar la pestaña tras descargar
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('download') === '1') {
                    setTimeout(() => {
                        window.close();
                    }, 1500);
                }
            }).catch(err => {
                console.error('Error al generar PDF:', err);
                if (btn) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            });
        }

        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('download') === '1') {
                // Descargar PDF automáticamente
                setTimeout(() => {
                    descargarPDF();
                }, 600);
            } else {
                // Diálogo de impresión normal
                setTimeout(() => {
                    window.print();
                }, 600);
            }
        });
    </script>
</body>
</html>
