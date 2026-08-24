<?php
$c = file_get_contents('save.php');
$c = str_replace("':concepto' => \$concepto,\n                    ':firma_base64' => \$firma_base64,", "':concepto' => \$concepto,\n                    ':rango_fechas' => \$rango_fechas,\n                    ':firma_base64' => \$firma_base64,", $c);
$c = str_replace("concepto, firma_base64,", "concepto, rango_fechas, firma_base64,", $c);
$c = str_replace(":concepto, :firma_base64,", ":concepto, :rango_fechas, :firma_base64,", $c);
file_put_contents('save.php', $c);
