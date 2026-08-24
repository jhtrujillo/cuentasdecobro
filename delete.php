<?php
/**
 * Controller to delete Invoices by ID
 */

require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Identificador no válido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM cuentas WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró el registro o ya fue eliminado.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de Base de Datos: ' . $e->getMessage()]);
}
?>
