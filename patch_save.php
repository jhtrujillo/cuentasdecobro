<?php
$content = file_get_contents('save.php');

// Add auto-add for rango_fechas
$migration = <<<'MIG'
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
MIG;
$content = str_replace('try {'."\n".'    $pdo->query("SELECT 1 FROM otros_ingresos LIMIT 1");', $migration, $content);

// Add extraction
$content = str_replace("\$concepto = trim(\$data['concepto']);", "\$concepto = trim(\$data['concepto']);\n            \$rango_fechas = isset(\$data['rango_fechas']) ? trim(\$data['rango_fechas']) : null;", $content);

// Add update fields
$content = str_replace("concepto = :concepto,\n                            firma_base64 = :firma_base64,", "concepto = :concepto,\n                            rango_fechas = :rango_fechas,\n                            firma_base64 = :firma_base64,", $content);

// Add update exec
$content = str_replace("':concepto' => \$concepto,\n                    ':firma_base64' => \$firma_base64,", "':concepto' => \$concepto,\n                    ':rango_fechas' => \$rango_fechas,\n                    ':firma_base64' => \$firma_base64,", $content);

// Add insert fields
$content = str_replace("concepto, firma_base64,", "concepto, rango_fechas, firma_base64,", $content);
$content = str_replace(":concepto, :firma_base64,", ":concepto, :rango_fechas, :firma_base64,", $content);

file_put_contents('save.php', $content);
echo "Patched save.php\n";
