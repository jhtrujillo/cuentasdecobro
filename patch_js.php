<?php
$c = file_get_contents('assets/js/script.js');

// Add rango_fechas to preview variables
$c = str_replace("const concepto = document.getElementById('cuenta-concepto').value", "const concepto = document.getElementById('cuenta-concepto').value || '';\n        const rangoFechas = document.getElementById('cuenta-rango-fechas') ? document.getElementById('cuenta-rango-fechas').value : '';", $c);

// Add to preview logic
$previewLogic = <<<'JS'
        document.getElementById('prev-concepto').innerText = concepto;
        
        if (rangoFechas && rangoFechas.trim() !== '') {
            document.getElementById('prev-rango-seccion').style.display = 'block';
            document.getElementById('prev-rango-fechas').innerText = rangoFechas;
        } else {
            const sec = document.getElementById('prev-rango-seccion');
            if (sec) sec.style.display = 'none';
        }
JS;
$c = str_replace("document.getElementById('prev-concepto').innerText = concepto;", $previewLogic, $c);

// Add to form submission
$c = str_replace("const concepto = document.getElementById('cuenta-concepto').value;", "const concepto = document.getElementById('cuenta-concepto').value;\n        const rango_fechas = document.getElementById('cuenta-rango-fechas') ? document.getElementById('cuenta-rango-fechas').value : '';", $c);

// Add to payload
$c = str_replace("payload.append('concepto', concepto);", "payload.append('concepto', concepto);\n        payload.append('rango_fechas', rango_fechas);", $c);

// Clear form
$c = str_replace("document.getElementById('cuenta-concepto').value = '';", "document.getElementById('cuenta-concepto').value = '';\n                if (document.getElementById('cuenta-rango-fechas')) document.getElementById('cuenta-rango-fechas').value = '';", $c);

// Edit populate
$c = str_replace("document.getElementById('cuenta-concepto').value = cuenta.concepto;", "document.getElementById('cuenta-concepto').value = cuenta.concepto;\n                if (document.getElementById('cuenta-rango-fechas')) document.getElementById('cuenta-rango-fechas').value = cuenta.rango_fechas || '';", $c);


file_put_contents('assets/js/script.js', $c);
