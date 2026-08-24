<?php
/**
 * Controller to save/update and list Invoices & Default Issuer
 */

require_once 'config.php';

// Auto-migration for gastos and otros_ingresos tables
try {
    $pdo->query("SELECT 1 FROM gastos LIMIT 1");
} catch (PDOException $e) {
    $sql = "CREATE TABLE IF NOT EXISTS gastos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha DATE NOT NULL,
        concepto VARCHAR(255) NOT NULL,
        valor DECIMAL(15, 2) NOT NULL,
        categoria VARCHAR(100) NOT NULL,
        ejecutado TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
}

// Auto-add column 'ejecutado' to existing 'gastos' table if it doesn't exist
try {
    $pdo->query("SELECT ejecutado FROM gastos LIMIT 1");
} catch (PDOException $e) {
    try {
        $pdo->exec("ALTER TABLE gastos ADD COLUMN ejecutado TINYINT(1) DEFAULT 0");
    } catch (PDOException $ex) {
        // ignore if already done
    }
}

// Auto-add column 'rango_fechas' to existing 'cuentas' table if it doesn't exist
try {
    $pdo->query("SELECT rango_fechas FROM cuentas LIMIT 1");
} catch (PDOException $e) {
    try {
        $pdo->exec("ALTER TABLE cuentas ADD COLUMN rango_fechas VARCHAR(255) NULL");
    } catch (PDOException $ex) {
        // ignore if already done
    }
}

try {
    $pdo->query("SELECT 1 FROM otros_ingresos LIMIT 1");
} catch (PDOException $e) {
    $sql = "CREATE TABLE IF NOT EXISTS otros_ingresos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha DATE NOT NULL,
        concepto VARCHAR(255) NOT NULL,
        valor DECIMAL(15, 2) NOT NULL,
        categoria VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
}

header('Content-Type: application/json; charset=utf-8');

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Parse JSON payload if sent via POST JSON
$data = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
}

try {
    switch ($action) {
        case 'list':
            // 1. Fetch Invoices
            $stmt = $pdo->query("SELECT * FROM cuentas ORDER BY numero_cuenta DESC");
            $cuentas = $stmt->fetchAll();

            // 2. Fetch default issuer
            $stmt = $pdo->query("SELECT * FROM emisores WHERE es_predeterminado = 1 LIMIT 1");
            $emisor = $stmt->fetch();

            // 3. Fetch registered clients (companies)
            $stmt = $pdo->query("SELECT * FROM clientes ORDER BY nombre ASC");
            $clientes = $stmt->fetchAll();

            // 4. Fetch expenses (gastos)
            $stmt = $pdo->query("SELECT * FROM gastos ORDER BY fecha DESC, id DESC");
            $gastos = $stmt->fetchAll();

            // 5. Fetch other income (otros_ingresos)
            $stmt = $pdo->query("SELECT * FROM otros_ingresos ORDER BY fecha DESC, id DESC");
            $otros_ingresos = $stmt->fetchAll();

            echo json_encode([
                'status' => 'success',
                'cuentas' => $cuentas,
                'emisor' => $emisor ? $emisor : null,
                'clientes' => $clientes,
                'gastos' => $gastos,
                'otros_ingresos' => $otros_ingresos
            ]);
            break;

        case 'save_cuenta':
            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => 'No se enviaron datos.']);
                exit;
            }

            // Extract variables
            $id = isset($data['id']) ? $data['id'] : null;
            $numero_cuenta = intval($data['numero_cuenta']);
            $fecha = $data['fecha'];
            $deudor_nombre = trim($data['deudor_nombre']);
            $deudor_nit = trim($data['deudor_nit']);
            $acreedor_nombre = trim($data['acreedor_nombre']);
            $acreedor_documento = trim($data['acreedor_documento']);
            $valor = floatval($data['valor']);
            $valor_letras = trim($data['valor_letras']);
            $concepto = trim($data['concepto']);
            $rango_fechas = isset($data['rango_fechas']) ? trim($data['rango_fechas']) : null;
            $firma_base64 = isset($data['firma_base64']) ? $data['firma_base64'] : null;
            $pagado = isset($data['pagado']) ? intval($data['pagado']) : 0;

            if (empty($numero_cuenta) || empty($fecha) || empty($deudor_nombre) || empty($deudor_nit) || 
                empty($acreedor_nombre) || empty($acreedor_documento) || empty($valor) || empty($concepto)) {
                echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
                exit;
            }

            if ($id) {
                // UPDATE Existing invoice
                $sql = "UPDATE cuentas SET 
                            numero_cuenta = :numero_cuenta,
                            fecha = :fecha,
                            deudor_nombre = :deudor_nombre,
                            deudor_nit = :deudor_nit,
                            acreedor_nombre = :acreedor_nombre,
                            acreedor_documento = :acreedor_documento,
                            valor = :valor,
                            valor_letras = :valor_letras,
                            concepto = :concepto,
                            rango_fechas = :rango_fechas,
                            firma_base64 = :firma_base64,
                            pagado = :pagado
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':numero_cuenta' => $numero_cuenta,
                    ':fecha' => $fecha,
                    ':deudor_nombre' => $deudor_nombre,
                    ':deudor_nit' => $deudor_nit,
                    ':acreedor_nombre' => $acreedor_nombre,
                    ':acreedor_documento' => $acreedor_documento,
                    ':valor' => $valor,
                    ':valor_letras' => $valor_letras,
                    ':concepto' => $concepto,
                    ':rango_fechas' => $rango_fechas,
                    ':firma_base64' => $firma_base64,
                    ':pagado' => $pagado,
                    ':id' => $id
                ]);
            } else {
                // INSERT New invoice
                $sql = "INSERT INTO cuentas 
                        (numero_cuenta, fecha, deudor_nombre, deudor_nit, acreedor_nombre, acreedor_documento, valor, valor_letras, concepto, rango_fechas, firma_base64, pagado)
                        VALUES 
                        (:numero_cuenta, :fecha, :deudor_nombre, :deudor_nit, :acreedor_nombre, :acreedor_documento, :valor, :valor_letras, :concepto, :rango_fechas, :firma_base64, :pagado)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':numero_cuenta' => $numero_cuenta,
                    ':fecha' => $fecha,
                    ':deudor_nombre' => $deudor_nombre,
                    ':deudor_nit' => $deudor_nit,
                    ':acreedor_nombre' => $acreedor_nombre,
                    ':acreedor_documento' => $acreedor_documento,
                    ':valor' => $valor,
                    ':valor_letras' => $valor_letras,
                    ':concepto' => $concepto,
                    ':rango_fechas' => $rango_fechas,
                    ':firma_base64' => $firma_base64,
                    ':pagado' => $pagado
                ]);
            }

            echo json_encode(['status' => 'success']);
            break;

        case 'toggle_pago':
            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => 'No se enviaron datos.']);
                exit;
            }
            $id = isset($data['id']) ? intval($data['id']) : null;
            $pagado = isset($data['pagado']) ? intval($data['pagado']) : 0;
            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID no válido.']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE cuentas SET pagado = :pagado WHERE id = :id");
            $stmt->execute([':pagado' => $pagado, ':id' => $id]);
            echo json_encode(['status' => 'success']);
            break;

        case 'save_emisor':
            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => 'No se enviaron datos del emisor.']);
                exit;
            }

            $nombre = trim($data['nombre']);
            $documento = trim($data['documento']);
            $banco = trim($data['banco']);
            $tipo_cuenta = trim($data['tipo_cuenta']);
            $numero_cuenta_bancaria = trim($data['numero_cuenta']);
            $firma_base64 = isset($data['firma_base64']) ? $data['firma_base64'] : null;

            if (empty($nombre) || empty($documento) || empty($banco) || empty($tipo_cuenta) || empty($numero_cuenta_bancaria)) {
                echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios del emisor.']);
                exit;
            }

            // Check if default emisor exists
            $stmt = $pdo->query("SELECT id FROM emisores WHERE es_predeterminado = 1 LIMIT 1");
            $existing = $stmt->fetch();

            if ($existing) {
                // Update
                $sql = "UPDATE emisores SET 
                            nombre = :nombre,
                            documento = :documento,
                            banco = :banco,
                            tipo_cuenta = :tipo_cuenta,
                            numero_cuenta = :numero_cuenta,
                            firma_base64 = :firma_base64
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':documento' => $documento,
                    ':banco' => $banco,
                    ':tipo_cuenta' => $tipo_cuenta,
                    ':numero_cuenta' => $numero_cuenta_bancaria,
                    ':firma_base64' => $firma_base64,
                    ':id' => $existing['id']
                ]);
            } else {
                // Insert new as default
                $sql = "INSERT INTO emisores 
                        (nombre, documento, banco, tipo_cuenta, numero_cuenta, firma_base64, es_predeterminado)
                        VALUES 
                        (:nombre, :documento, :banco, :tipo_cuenta, :numero_cuenta, :firma_base64, 1)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':documento' => $documento,
                    ':banco' => $banco,
                    ':tipo_cuenta' => $tipo_cuenta,
                    ':numero_cuenta' => $numero_cuenta_bancaria,
                    ':firma_base64' => $firma_base64
                ]);
            }

            echo json_encode(['status' => 'success']);
            break;

        case 'save_cliente':
            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => 'No se enviaron datos.']);
                exit;
            }

            $id = isset($data['id']) ? intval($data['id']) : null;
            $nombre = trim($data['nombre']);
            $nit = trim($data['nit']);

            if (empty($nombre) || empty($nit)) {
                echo json_encode(['status' => 'error', 'message' => 'El nombre y NIT del cliente son obligatorios.']);
                exit;
            }

            if ($id) {
                $sql = "UPDATE clientes SET nombre = :nombre, nit = :nit WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':nit' => $nit,
                    ':id' => $id
                ]);
            } else {
                $sql = "INSERT INTO clientes (nombre, nit) VALUES (:nombre, :nit)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':nit' => $nit
                ]);
            }

            echo json_encode(['status' => 'success']);
            break;

        case 'delete_cliente':
            $id = isset($data['id']) ? intval($data['id']) : (isset($_POST['id']) ? intval($_POST['id']) : null);
            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID de cliente no válido.']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['status' => 'success']);
            break;

        case 'save_gasto':
            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => 'No se enviaron datos del gasto.']);
                exit;
            }

            $id = isset($data['id']) ? intval($data['id']) : null;
            $fecha = trim($data['fecha']);
            $concepto = trim($data['concepto']);
            $valor = floatval($data['valor']);
            $categoria = trim($data['categoria']);
            $ejecutado = isset($data['ejecutado']) ? intval($data['ejecutado']) : 0;

            if (empty($fecha) || empty($concepto) || empty($valor) || empty($categoria)) {
                echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios para el gasto.']);
                exit;
            }

            if ($id) {
                $sql = "UPDATE gastos SET fecha = :fecha, concepto = :concepto, valor = :valor, categoria = :categoria, ejecutado = :ejecutado WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':fecha' => $fecha,
                    ':concepto' => $concepto,
                    ':valor' => $valor,
                    ':categoria' => $categoria,
                    ':ejecutado' => $ejecutado,
                    ':id' => $id
                ]);
            } else {
                $sql = "INSERT INTO gastos (fecha, concepto, valor, categoria, ejecutado) VALUES (:fecha, :concepto, :valor, :categoria, :ejecutado)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':fecha' => $fecha,
                    ':concepto' => $concepto,
                    ':valor' => $valor,
                    ':categoria' => $categoria,
                    ':ejecutado' => $ejecutado
                ]);
            }

            echo json_encode(['status' => 'success']);
            break;

        case 'delete_gasto':
            if (!$data) {
                $id = isset($_POST['id']) ? intval($_POST['id']) : null;
            } else {
                $id = isset($data['id']) ? intval($data['id']) : null;
            }

            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID de gasto no válido.']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM gastos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['status' => 'success']);
            break;

        case 'toggle_gasto_ejecutado':
            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => 'No se enviaron datos.']);
                exit;
            }
            $id = isset($data['id']) ? intval($data['id']) : null;
            $ejecutado = isset($data['ejecutado']) ? intval($data['ejecutado']) : 0;
            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID no válido.']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE gastos SET ejecutado = :ejecutado WHERE id = :id");
            $stmt->execute([':ejecutado' => $ejecutado, ':id' => $id]);
            echo json_encode(['status' => 'success']);
            break;

        case 'save_ingreso':
            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => 'No se enviaron datos del ingreso.']);
                exit;
            }

            $id = isset($data['id']) ? intval($data['id']) : null;
            $fecha = trim($data['fecha']);
            $concepto = trim($data['concepto']);
            $valor = floatval($data['valor']);
            $categoria = trim($data['categoria']);

            if (empty($fecha) || empty($concepto) || empty($valor) || empty($categoria)) {
                echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios para el ingreso.']);
                exit;
            }

            if ($id) {
                $sql = "UPDATE otros_ingresos SET fecha = :fecha, concepto = :concepto, valor = :valor, categoria = :categoria WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':fecha' => $fecha,
                    ':concepto' => $concepto,
                    ':valor' => $valor,
                    ':categoria' => $categoria,
                    ':id' => $id
                ]);
            } else {
                $sql = "INSERT INTO otros_ingresos (fecha, concepto, valor, categoria) VALUES (:fecha, :concepto, :valor, :categoria)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':fecha' => $fecha,
                    ':concepto' => $concepto,
                    ':valor' => $valor,
                    ':categoria' => $categoria
                ]);
            }

            echo json_encode(['status' => 'success']);
            break;

        case 'delete_ingreso':
            if (!$data) {
                $id = isset($_POST['id']) ? intval($_POST['id']) : null;
            } else {
                $id = isset($data['id']) ? intval($data['id']) : null;
            }

            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID de ingreso no válido.']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM otros_ingresos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['status' => 'success']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción no permitida.']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de Base de Datos: ' . $e->getMessage()]);
}
?>
