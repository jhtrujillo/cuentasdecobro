/**
 * Cuentas de Cobro - Dynamic UI and Business Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    // State management
    let dbCuentas = [];
    let defaultEmisor = null;
    let dbClientes = [];
    let dbGastos = [];
    let dbOtrosIngresos = [];
    let signatureMode = 'draw'; // 'draw' or 'upload'
    let currentFirmaBase64 = null;
    let eSignatureMode = 'draw';
    let eCurrentFirmaBase64 = null;
    
    // Canvas Signature Setup
    const canvas = document.getElementById('signature-canvas');
    let ctx = null;
    if (canvas) {
        ctx = canvas.getContext('2d');
        setupCanvasDrawing(
            canvas, 
            ctx, 
            syncSignatureToPreview, 
            'clear-sig-btn', 
            () => {
                currentFirmaBase64 = null;
                document.getElementById('preview-firma-img').style.display = 'none';
                document.getElementById('preview-firma-img').src = '';
            }
        );
    }

    // Load initial data
    loadDashboardData();

    // Event Listeners
    setupEventListeners();

    // ----------------------------------------------------
    // Number-to-Words Algorithm (Spanish / Pesos Colombianos)
    // ----------------------------------------------------
    function traducirTresDigitos(n) {
        if (n === 0) return '';
        
        const unidades = ['', 'un', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
        const decenas = ['', 'diez', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        const especiales = {
            11: 'once', 12: 'doce', 13: 'trece', 14: 'catorce', 15: 'quince',
            16: 'dieciséis', 17: 'diecisiete', 18: 'dieciocho', 19: 'diecinueve',
            21: 'veintiún', 22: 'veintidós', 23: 'veintitrés', 24: 'veinticuatro',
            25: 'veinticinco', 26: 'veintiséis', 27: 'veintisiete', 28: 'veintiocho', 29: 'veintinueve'
        };
        const centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
        
        if (n === 100) return 'cien';
        
        let c = Math.floor(n / 100);
        let restoCentenas = n % 100;
        let result = centenas[c];
        
        if (restoCentenas > 0) {
            if (result !== '') result += ' ';
            
            if (restoCentenas < 10) {
                result += unidades[restoCentenas];
            } else if (especiales[restoCentenas]) {
                result += especiales[restoCentenas];
            } else {
                let d = Math.floor(restoCentenas / 10);
                let u = restoCentenas % 10;
                result += decenas[d];
                if (u > 0) {
                    result += ' y ' + unidades[u];
                }
            }
        }
        return result;
    }

    function numeroALetras(num) {
        num = Math.floor(num);
        if (isNaN(num) || num === 0) return 'Cero pesos';
        if (num === 1) return 'Un peso';
        
        let letras = '';
        
        let milesMillones = Math.floor(num / 1000000000);
        let restoMilesMillones = num % 1000000000;
        
        if (milesMillones > 0) {
            if (milesMillones === 1) {
                letras += 'mil millones ';
            } else {
                letras += traducirTresDigitos(milesMillones) + ' mil millones ';
            }
        }
        
        let millones = Math.floor(restoMilesMillones / 1000000);
        let restoMillones = restoMilesMillones % 1000000;
        
        if (millones > 0) {
            if (millones === 1) {
                letras += 'un millón ';
            } else {
                letras += traducirTresDigitos(millones) + ' millones ';
            }
        }
        
        let miles = Math.floor(restoMillones / 1000);
        let restoMiles = restoMillones % 1000;
        
        if (miles > 0) {
            if (miles === 1) {
                letras += 'mil ';
            } else {
                letras += traducirTresDigitos(miles) + ' mil ';
            }
        }
        
        if (restoMiles > 0) {
            letras += traducirTresDigitos(restoMiles) + ' ';
        }
        
        letras = letras.trim();
        
        // Rules for "de pesos" (ends in clean millions or billions)
        if (num >= 1000000 && num % 1000000 === 0) {
            letras += ' de pesos';
        } else {
            letras += ' pesos';
        }
        
        return letras.charAt(0).toUpperCase() + letras.slice(1);
    }

    // Formatter for Currency COP (e.g. $4.741.188)
    function formatCOP(valor) {
        if (!valor || isNaN(valor)) return '$0';
        return '$' + new Intl.NumberFormat('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(valor);
    }

    // ----------------------------------------------------
    // Signature Drawing Pad (Mouse and Touch Support)
    // ----------------------------------------------------
    function setupCanvasDrawing(canvas, ctx, onDraw = null, clearBtnId = null, onClear = null) {
        let drawing = false;
        
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        
        function getMousePos(e) {
            const rect = canvas.getBoundingClientRect();
            // Calculate relative coordinates (accounting for canvas CSS size scaling)
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function startDrawing(e) {
            e.preventDefault();
            drawing = true;
            const pos = getMousePos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!drawing) return;
            e.preventDefault();
            const pos = getMousePos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            
            if (onDraw) {
                onDraw();
            }
        }

        function stopDrawing(e) {
            if (drawing) {
                ctx.closePath();
                drawing = false;
            }
        }

        // Mouse Events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        // Touch Events (Mobile/Tablet)
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);

        // Clear Canvas Button
        if (clearBtnId) {
            const clearBtn = document.getElementById(clearBtnId);
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    if (onClear) {
                        onClear();
                    }
                });
            }
        }
    }

    function syncSignatureToPreview() {
        if (signatureMode === 'draw') {
            // Check if canvas is empty
            const blank = document.createElement('canvas');
            blank.width = canvas.width;
            blank.height = canvas.height;
            if (canvas.toDataURL() !== blank.toDataURL()) {
                currentFirmaBase64 = canvas.toDataURL('image/png');
                const prevImg = document.getElementById('preview-firma-img');
                prevImg.src = currentFirmaBase64;
                prevImg.style.display = 'block';
            } else {
                currentFirmaBase64 = null;
                document.getElementById('preview-firma-img').style.display = 'none';
            }
        }
    }

    // ----------------------------------------------------
    // Live Invoice Preview Syncing
    // ----------------------------------------------------
    function updateLivePreview() {
        const numero = document.getElementById('cuenta-numero').value || '1';
        const deudorNombre = document.getElementById('deudor-nombre').value || '[Nombre del Cliente/Deudor]';
        const deudorNit = document.getElementById('deudor-nit').value || '[NIT/C.C. del Deudor]';
        const acreedorNombre = document.getElementById('acreedor-nombre').value || '[Nombre del Emisor/Acreedor]';
        const acreedorDoc = document.getElementById('acreedor-documento').value || '[Documento del Emisor/Acreedor]';
        const valorInput = parseFloat(document.getElementById('cuenta-valor').value) || 0;
        const concepto = document.getElementById('cuenta-concepto').value || '[Descripción detallada del cobro y datos bancarios]';
        
        // Helper to format company document (Nit vs CC)
        function formatDocumento(doc) {
            if (!doc) return '';
            const docClean = doc.trim();
            const docLower = docClean.toLowerCase();
            
            if (docLower.startsWith('nit') || docLower.startsWith('cc') || docLower.startsWith('c.c') || docLower.startsWith('cedula') || docLower.startsWith('c.e')) {
                return docClean;
            }
            
            if (docClean.includes('-')) {
                return 'Nit. ' + docClean;
            }
            
            return 'C.C. ' + docClean;
        }

        // Auto-convert number to letters
        const letras = numeroALetras(valorInput);
        document.getElementById('cuenta-valor-letras').value = letras;
        
        // Format preview text
        document.getElementById('prev-num').innerText = numero;
        document.getElementById('prev-deudor-nombre').innerText = deudorNombre;
        document.getElementById('prev-deudor-nit').innerText = formatDocumento(deudorNit);
        document.getElementById('prev-acreedor-nombre-debe').innerText = acreedorNombre;
        document.getElementById('prev-acreedor-doc-debe').innerText = acreedorDoc;
        
        document.getElementById('prev-letras').innerText = letras;
        document.getElementById('prev-valor-num').innerText = '(' + formatCOP(valorInput) + ')';
        document.getElementById('prev-concepto').innerText = concepto;
        
        document.getElementById('prev-firma-nombre').innerText = acreedorNombre;
        document.getElementById('prev-firma-doc').innerText = acreedorDoc;
        
        // Show active signature
        const prevImg = document.getElementById('preview-firma-img');
        if (currentFirmaBase64) {
            prevImg.src = currentFirmaBase64;
            prevImg.style.display = 'block';
        } else {
            prevImg.style.display = 'none';
        }

        // Show live paid stamp
        const pagadoInput = document.getElementById('cuenta-pagado');
        const prevPaidStamp = document.getElementById('prev-paid-stamp');
        if (prevPaidStamp) {
            const isPagado = pagadoInput ? pagadoInput.value === '1' : false;
            prevPaidStamp.style.display = isPagado ? 'block' : 'none';
        }
    }

    // ----------------------------------------------------
    // Event Listeners Setup
    // ----------------------------------------------------
    function setupEventListeners() {
        // Form field changes update preview in real-time
        const formInputs = [
            'cuenta-numero', 'deudor-nombre', 'deudor-nit', 
            'acreedor-nombre', 'acreedor-documento', 'cuenta-valor', 'cuenta-concepto', 'cuenta-pagado'
        ];
        formInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', updateLivePreview);
                if (id === 'cuenta-pagado') {
                    input.addEventListener('change', updateLivePreview);
                }
            }
        });

        // Modal triggers
        const btnNueva = document.getElementById('btn-nueva-cuenta');
        const modalCuenta = document.getElementById('modal-cuenta');
        const closeModalButtons = document.querySelectorAll('.close-btn, .btn-close-modal');

        if (btnNueva) {
            btnNueva.addEventListener('click', () => {
                openCuentaModal('create');
            });
        }

        closeModalButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                modalCuenta.classList.remove('active');
                document.getElementById('modal-emisor').classList.remove('active');
                document.getElementById('modal-clientes').classList.remove('active');
                document.getElementById('modal-gasto').classList.remove('active');
                document.getElementById('modal-ingreso').classList.remove('active');
            });
        });

        // Close on background click
        window.addEventListener('click', (e) => {
            if (e.target === modalCuenta) {
                modalCuenta.classList.remove('active');
            }
            const modalEmisor = document.getElementById('modal-emisor');
            if (e.target === modalEmisor) {
                modalEmisor.classList.remove('active');
            }
            const modalClientes = document.getElementById('modal-clientes');
            if (e.target === modalClientes) {
                modalClientes.classList.remove('active');
            }
            const modalGasto = document.getElementById('modal-gasto');
            if (e.target === modalGasto) {
                modalGasto.classList.remove('active');
            }
            const modalIngreso = document.getElementById('modal-ingreso');
            if (e.target === modalIngreso) {
                modalIngreso.classList.remove('active');
            }
        });

        // Signature tabs
        const tabDraw = document.getElementById('tab-sig-draw');
        const tabUpload = document.getElementById('tab-sig-upload');
        const paneDraw = document.getElementById('pane-sig-draw');
        const paneUpload = document.getElementById('pane-sig-upload');
        const sigFileInput = document.getElementById('signature-file');

        if (tabDraw && tabUpload) {
            tabDraw.addEventListener('click', () => {
                tabDraw.classList.add('active');
                tabUpload.classList.remove('active');
                paneDraw.classList.add('active');
                paneUpload.classList.remove('active');
                signatureMode = 'draw';
                syncSignatureToPreview();
            });

            tabUpload.addEventListener('click', () => {
                tabUpload.classList.add('active');
                tabDraw.classList.remove('active');
                paneUpload.classList.add('active');
                paneDraw.classList.remove('active');
                signatureMode = 'upload';
                
                // Show uploaded preview if file exists
                const filePreview = document.getElementById('signature-upload-preview');
                if (filePreview && filePreview.src && filePreview.style.display === 'block') {
                    currentFirmaBase64 = filePreview.src;
                } else {
                    currentFirmaBase64 = null;
                }
                updateLivePreview();
            });
        }

        // Handle signature file upload (convert to Base64)
        if (sigFileInput) {
            sigFileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    if (!file.type.startsWith('image/')) {
                        showToast('Por favor, selecciona un archivo de imagen válido (PNG, JPG).', 'danger');
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        currentFirmaBase64 = evt.target.result;
                        const filePreview = document.getElementById('signature-upload-preview');
                        filePreview.src = currentFirmaBase64;
                        filePreview.style.display = 'block';
                        
                        // Sync to live preview
                        updateLivePreview();
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Configure default issuer button
        const btnEmisor = document.getElementById('btn-config-emisor');
        const modalEmisor = document.getElementById('modal-emisor');
        if (btnEmisor) {
            btnEmisor.addEventListener('click', () => {
                openEmisorModal();
            });
        }

        // Submit default issuer form
        const formEmisor = document.getElementById('form-emisor');
        if (formEmisor) {
            formEmisor.addEventListener('submit', (e) => {
                e.preventDefault();
                saveDefaultEmisor();
            });
        }

        // Submit main invoice form
        const formCuenta = document.getElementById('form-cuenta');
        if (formCuenta) {
            formCuenta.addEventListener('submit', (e) => {
                e.preventDefault();
                saveCuentaCobro();
            });
        }

        // Search filtering in table
        const searchInput = document.getElementById('table-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                filterTable(query);
            });
        }

        // Autocomplete dropdown change listener
        const selectDeudor = document.getElementById('select-deudor');
        if (selectDeudor) {
            selectDeudor.addEventListener('change', (e) => {
                const val = e.target.value;
                if (val) {
                    const client = dbClientes.find(c => c.id == val);
                    if (client) {
                        document.getElementById('deudor-nombre').value = client.nombre;
                        document.getElementById('deudor-nit').value = client.nit;
                        updateLivePreview();
                    }
                }
            });
        }

        // Manage clients button click
        const btnClientes = document.getElementById('btn-config-clientes');
        const modalClientes = document.getElementById('modal-clientes');
        if (btnClientes && modalClientes) {
            btnClientes.addEventListener('click', () => {
                openClientesModal();
            });
        }

        // Client form submit
        const formCliente = document.getElementById('form-cliente');
        if (formCliente) {
            formCliente.addEventListener('submit', (e) => {
                e.preventDefault();
                saveCliente();
            });
        }

        // Cancel editing client
        const btnCancelCliente = document.getElementById('btn-cancelar-cliente');
        if (btnCancelCliente) {
            btnCancelCliente.addEventListener('click', () => {
                document.getElementById('cliente-id').value = '';
                document.getElementById('cliente-nombre').value = '';
                document.getElementById('cliente-nit').value = '';
                document.getElementById('form-cliente-title').innerText = 'Registrar Nuevo Cliente';
                btnCancelCliente.style.display = 'none';
            });
        }

        // Close modal buttons for all modals (including modal-clientes)
        const closeModals = document.querySelectorAll('.close-btn, .btn-close-modal');
        closeModals.forEach(btn => {
            btn.addEventListener('click', () => {
                modalClientes.classList.remove('active');
            });
        });

        // Emisor modal signature draw tab setups
        const eTabDraw = document.getElementById('e-tab-sig-draw');
        const eTabUpload = document.getElementById('e-tab-sig-upload');
        const ePaneDraw = document.getElementById('e-pane-sig-draw');
        const ePaneUpload = document.getElementById('e-pane-sig-upload');
        const eCanvas = document.getElementById('e-signature-canvas');
        const eFileInput = document.getElementById('e-signature-file');
        
        let eCtx = null;

        if (eCanvas) {
            eCtx = eCanvas.getContext('2d');
            setupCanvasDrawing(
                eCanvas, 
                eCtx, 
                null, 
                'e-clear-sig-btn', 
                () => {
                    eCurrentFirmaBase64 = null;
                }
            );
        }

        if (eTabDraw && eTabUpload) {
            eTabDraw.addEventListener('click', () => {
                eTabDraw.classList.add('active');
                eTabUpload.classList.remove('active');
                ePaneDraw.classList.add('active');
                ePaneUpload.classList.remove('active');
                eSignatureMode = 'draw';
            });

            eTabUpload.addEventListener('click', () => {
                eTabUpload.classList.add('active');
                eTabDraw.classList.remove('active');
                ePaneUpload.classList.add('active');
                ePaneDraw.classList.remove('active');
                eSignatureMode = 'upload';
                
                // Show uploaded preview if file exists
                const filePreview = document.getElementById('e-signature-upload-preview');
                if (filePreview && filePreview.src && filePreview.style.display === 'block') {
                    eCurrentFirmaBase64 = filePreview.src;
                } else {
                    eCurrentFirmaBase64 = null;
                }
            });
        }

        if (eFileInput) {
            eFileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        eCurrentFirmaBase64 = evt.target.result;
                        const filePreview = document.getElementById('e-signature-upload-preview');
                        filePreview.src = eCurrentFirmaBase64;
                        filePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    // ----------------------------------------------------
    // Modal Openers
    // ----------------------------------------------------
    function openCuentaModal(action, id = null) {
        const modal = document.getElementById('modal-cuenta');
        const form = document.getElementById('form-cuenta');
        const modalTitle = document.getElementById('modal-cuenta-title');
        
        form.reset();
        currentFirmaBase64 = null;
        
        // Reset signature canvas
        if (canvas && ctx) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
        
        // Reset upload preview
        document.getElementById('signature-upload-preview').style.display = 'none';
        document.getElementById('signature-upload-preview').src = '';
        
        // Default tabs
        document.getElementById('tab-sig-draw').click();

        if (action === 'create') {
            modalTitle.innerText = 'Crear Nueva Cuenta de Cobro';
            document.getElementById('cuenta-id').value = '';
            
            const pagadoSelect = document.getElementById('cuenta-pagado');
            if (pagadoSelect) pagadoSelect.value = '0';
            
            // Auto-calculate next invoice number
            let nextNum = 1;
            if (dbCuentas.length > 0) {
                const maxNum = Math.max(...dbCuentas.map(c => parseInt(c.numero_cuenta) || 0));
                nextNum = maxNum + 1;
            }
            document.getElementById('cuenta-numero').value = nextNum;
            
            // Auto-fill today's date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('cuenta-fecha').value = today;

            // Auto-fill issuer if default exists
            if (defaultEmisor) {
                document.getElementById('acreedor-nombre').value = defaultEmisor.nombre;
                document.getElementById('acreedor-documento').value = defaultEmisor.documento;
                
                // Format default concept
                const defaultConcept = `Por prestación de servicios de: [Descripción del servicio]. \n\nPor favor, realizar el pago a la cuenta de ${defaultEmisor.tipo_cuenta} N° ${defaultEmisor.numero_cuenta} de ${defaultEmisor.banco}.`;
                document.getElementById('cuenta-concepto').value = defaultConcept;
                
                if (defaultEmisor.firma_base64) {
                    currentFirmaBase64 = defaultEmisor.firma_base64;
                    const filePreview = document.getElementById('signature-upload-preview');
                    filePreview.src = currentFirmaBase64;
                    filePreview.style.display = 'block';
                    document.getElementById('tab-sig-upload').click();
                }
            } else {
                document.getElementById('cuenta-concepto').value = '';
            }
        } else if (action === 'edit' && id) {
            modalTitle.innerText = 'Editar Cuenta de Cobro';
            const cuenta = dbCuentas.find(c => c.id == id);
            if (cuenta) {
                document.getElementById('cuenta-id').value = cuenta.id;
                document.getElementById('cuenta-numero').value = cuenta.numero_cuenta;
                document.getElementById('cuenta-fecha').value = cuenta.fecha;
                document.getElementById('deudor-nombre').value = cuenta.deudor_nombre;
                document.getElementById('deudor-nit').value = cuenta.deudor_nit;
                document.getElementById('acreedor-nombre').value = cuenta.acreedor_nombre;
                document.getElementById('acreedor-documento').value = cuenta.acreedor_documento;
                document.getElementById('cuenta-valor').value = cuenta.valor;
                document.getElementById('cuenta-valor-letras').value = cuenta.valor_letras;
                document.getElementById('cuenta-concepto').value = cuenta.concepto;
                
                const pagadoSelect = document.getElementById('cuenta-pagado');
                if (pagadoSelect) pagadoSelect.value = cuenta.pagado || '0';
                
                if (cuenta.firma_base64) {
                    currentFirmaBase64 = cuenta.firma_base64;
                    const filePreview = document.getElementById('signature-upload-preview');
                    filePreview.src = currentFirmaBase64;
                    filePreview.style.display = 'block';
                    document.getElementById('tab-sig-upload').click();
                }
            }
        }
        
        updateLivePreview();
        modal.classList.add('active');
    }

    function openEmisorModal() {
        const modal = document.getElementById('modal-emisor');
        const form = document.getElementById('form-emisor');
        
        form.reset();
        
        // Reset file preview
        document.getElementById('e-signature-upload-preview').style.display = 'none';
        document.getElementById('e-signature-upload-preview').src = '';
        
        // Default tab
        document.getElementById('e-tab-sig-draw').click();
        const eCanvas = document.getElementById('e-signature-canvas');
        if (eCanvas) {
            const eCtx = eCanvas.getContext('2d');
            eCtx.clearRect(0, 0, eCanvas.width, eCanvas.height);
        }

        if (defaultEmisor) {
            document.getElementById('emisor-nombre').value = defaultEmisor.nombre;
            document.getElementById('emisor-documento').value = defaultEmisor.documento;
            document.getElementById('emisor-banco').value = defaultEmisor.banco;
            document.getElementById('emisor-tipo-cuenta').value = defaultEmisor.tipo_cuenta;
            document.getElementById('emisor-numero-cuenta').value = defaultEmisor.numero_cuenta;
            
            if (defaultEmisor.firma_base64) {
                eCurrentFirmaBase64 = defaultEmisor.firma_base64;
                const filePreview = document.getElementById('e-signature-upload-preview');
                filePreview.src = defaultEmisor.firma_base64;
                filePreview.style.display = 'block';
                document.getElementById('e-tab-sig-upload').click();
            }
        }
        
        modal.classList.add('active');
    }

    // ----------------------------------------------------
    // Database Operations (CRUD via AJAX)
    // ----------------------------------------------------
    // Helper to safely execute fetch and parse JSON or extract PHP server errors
    function requestAPI(url, options = {}) {
        return fetch(url, options)
            .then(res => {
                return res.text().then(text => {
                    try {
                        const json = JSON.parse(text);
                        return { ok: res.ok, data: json, text: text };
                    } catch (e) {
                        return { ok: false, data: null, text: text };
                    }
                });
            })
            .catch(err => {
                return { ok: false, data: null, text: err.message, isNetworkError: true };
            });
    }

    function loadDashboardData() {
        requestAPI('save.php?action=list')
            .then(res => {
                if (res.ok && res.data && res.data.status === 'success') {
                    dbCuentas = res.data.cuentas || [];
                    defaultEmisor = res.data.emisor || null;
                    dbClientes = res.data.clientes || [];
                    dbGastos = res.data.gastos || [];
                    dbOtrosIngresos = res.data.otros_ingresos || [];
                    renderTable(dbCuentas);
                    updateDashboardStats(dbCuentas);
                    renderClientSelect(dbClientes);
                    renderClientList(dbClientes);
                    
                    // Render finances as well
                    renderFinanceTables();
                    updateFinanceStats();
                } else {
                    const errMsg = res.text ? res.text.replace(/<[^>]*>/g, '').trim().substring(0, 150) : 'Error desconocido';
                    showToast('Error de servidor: ' + errMsg, 'danger');
                }
            });
    }

    function saveCuentaCobro() {
        const id = document.getElementById('cuenta-id').value;
        const numero = document.getElementById('cuenta-numero').value;
        const fecha = document.getElementById('cuenta-fecha').value;
        const deudorNombre = document.getElementById('deudor-nombre').value;
        const deudorNit = document.getElementById('deudor-nit').value;
        const acreedorNombre = document.getElementById('acreedor-nombre').value;
        const acreedorDoc = document.getElementById('acreedor-documento').value;
        const valor = document.getElementById('cuenta-valor').value;
        const valorLetras = document.getElementById('cuenta-valor-letras').value;
        const concepto = document.getElementById('cuenta-concepto').value;
        const pagadoSelect = document.getElementById('cuenta-pagado');
        const pagado = pagadoSelect ? pagadoSelect.value : '0';

        // Ensure current signature is synced before saving
        syncSignatureToPreview();

        if (!numero || !fecha || !deudorNombre || !deudorNit || !acreedorNombre || !acreedorDoc || !valor || !concepto) {
            showToast('Por favor, rellene todos los campos obligatorios.', 'danger');
            return;
        }

        const payload = {
            id: id || null,
            numero_cuenta: numero,
            fecha: fecha,
            deudor_nombre: deudorNombre,
            deudor_nit: deudorNit,
            acreedor_nombre: acreedorNombre,
            acreedor_documento: acreedorDoc,
            valor: valor,
            valor_letras: valorLetras,
            concepto: concepto,
            firma_base64: currentFirmaBase64,
            pagado: pagado
        };

        requestAPI('save.php?action=save_cuenta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => {
            if (res.ok && res.data && res.data.status === 'success') {
                showToast(id ? 'Cuenta de cobro actualizada con éxito.' : 'Cuenta de cobro creada con éxito.', 'success');
                document.getElementById('modal-cuenta').classList.remove('active');
                loadDashboardData();
            } else {
                const errMsg = res.text ? res.text.replace(/<[^>]*>/g, '').trim().substring(0, 150) : (res.data ? res.data.message : 'Error desconocido');
                showToast('Error al guardar: ' + errMsg, 'danger');
            }
        });
    }

    function saveDefaultEmisor() {
        const nombre = document.getElementById('emisor-nombre').value;
        const documento = document.getElementById('emisor-documento').value;
        const banco = document.getElementById('emisor-banco').value;
        const tipoCuenta = document.getElementById('emisor-tipo-cuenta').value;
        const numeroCuenta = document.getElementById('emisor-numero-cuenta').value;

        // Fetch signature from active tab in emisor modal
        let emisorFirma = null;
        const eCanvas = document.getElementById('e-signature-canvas');
        
        if (eSignatureMode === 'upload') {
            emisorFirma = eCurrentFirmaBase64;
        } else {
            // Draw mode
            const blank = document.createElement('canvas');
            blank.width = eCanvas.width;
            blank.height = eCanvas.height;
            if (eCanvas.toDataURL() !== blank.toDataURL()) {
                emisorFirma = eCanvas.toDataURL('image/png');
            }
        }

        if (!nombre || !documento || !banco || !tipoCuenta || !numeroCuenta) {
            showToast('Por favor, rellene todos los campos obligatorios del emisor.', 'danger');
            return;
        }

        const payload = {
            nombre: nombre,
            documento: documento,
            banco: banco,
            tipo_cuenta: tipoCuenta,
            numero_cuenta: numeroCuenta,
            firma_base64: emisorFirma
        };

        requestAPI('save.php?action=save_emisor', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => {
            if (res.ok && res.data && res.data.status === 'success') {
                showToast('Datos del emisor predeterminado guardados con éxito.', 'success');
                document.getElementById('modal-emisor').classList.remove('active');
                loadDashboardData();
            } else {
                const errMsg = res.text ? res.text.replace(/<[^>]*>/g, '').trim().substring(0, 150) : (res.data ? res.data.message : 'Error desconocido');
                showToast('Error al guardar datos del emisor: ' + errMsg, 'danger');
            }
        });
    }

    window.confirmDeleteCuenta = function(id) {
        if (confirm('¿Estás seguro de que deseas eliminar esta cuenta de cobro? Esta acción no se puede deshacer.')) {
            requestAPI('delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(res => {
                if (res.ok && res.data && res.data.status === 'success') {
                    showToast('Cuenta de cobro eliminada con éxito.', 'success');
                    loadDashboardData();
                } else {
                    const errMsg = res.text ? res.text.replace(/<[^>]*>/g, '').trim().substring(0, 150) : (res.data ? res.data.message : 'Error desconocido');
                    showToast('Error al eliminar: ' + errMsg, 'danger');
                }
            });
        }
    };

    window.editCuenta = function(id) {
        openCuentaModal('edit', id);
    };

    window.togglePagoStatus = function(id, currentStatus) {
        const nextStatus = parseInt(currentStatus) === 1 ? 0 : 1;
        requestAPI('save.php?action=toggle_pago', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, pagado: nextStatus })
        })
        .then(res => {
            if (res.ok && res.data && res.data.status === 'success') {
                showToast(nextStatus === 1 ? 'Cuenta marcada como Cobrada.' : 'Cuenta marcada como Pendiente.', 'success');
                loadDashboardData();
            } else {
                const errMsg = res.text ? res.text.replace(/<[^>]*>/g, '').trim().substring(0, 150) : (res.data ? res.data.message : 'Error desconocido');
                showToast('Error al cambiar estado: ' + errMsg, 'danger');
            }
        });
    };

    // ----------------------------------------------------
    // Dynamic Rendering Helpers
    // ----------------------------------------------------
    function renderTable(cuentas) {
        const tbody = document.getElementById('table-body');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (cuentas.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3>No hay cuentas creadas</h3>
                            <p>Comienza creando tu primera cuenta de cobro pulsando el botón "Nueva Cuenta".</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }
        
        // Sort accounts by number descending
        cuentas.sort((a, b) => parseInt(b.numero_cuenta) - parseInt(a.numero_cuenta));

        cuentas.forEach(c => {
            const isPagado = parseInt(c.pagado) === 1;
            const badgeClass = isPagado ? 'badge-success' : 'badge-warning';
            const badgeText = isPagado ? 'Cobrado' : 'Pendiente';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong># ${c.numero_cuenta}</strong></td>
                <td>
                    <div style="font-weight: 600;">${escapeHtml(c.deudor_nombre)}</div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">NIT: ${escapeHtml(c.deudor_nit)}</div>
                </td>
                <td>
                    <div style="font-weight: 600;">${escapeHtml(c.acreedor_nombre)}</div>
                </td>
                <td style="font-weight: 600; color: #3b82f6;">${formatCOP(c.valor)}</td>
                <td><span class="badge ${badgeClass}">${badgeText}</span></td>
                <td>${formatDate(c.fecha)}</td>
                <td>
                    <div class="actions-cell">
                        <button onclick="togglePagoStatus(${c.id}, ${c.pagado})" class="btn btn-secondary btn-sm" title="${isPagado ? 'Marcar como Pendiente' : 'Marcar como Cobrado'}" style="color: ${isPagado ? '#10b981' : 'var(--text-secondary)'};">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <a href="print.php?id=${c.id}" target="_blank" class="btn btn-secondary btn-sm" title="Ver / Imprimir">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                        </a>
                        <a href="print.php?id=${c.id}&download=1" target="_blank" class="btn btn-secondary btn-sm" title="Descargar PDF" style="background-color: #10b981; border-color: #10b981; color: white;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2-8H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7l-4-4z" />
                            </svg>
                        </a>
                        <button onclick="editCuenta(${c.id})" class="btn btn-secondary btn-sm" title="Editar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button onclick="confirmDeleteCuenta(${c.id})" class="btn btn-danger btn-sm" title="Eliminar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function updateDashboardStats(cuentas) {
        const totalCobrado = cuentas.reduce((sum, c) => sum + (parseInt(c.pagado) === 1 ? parseFloat(c.valor) : 0), 0);
        const totalPendiente = cuentas.reduce((sum, c) => sum + (parseInt(c.pagado) !== 1 ? parseFloat(c.valor) : 0), 0);
        
        const cobradoEl = document.getElementById('stat-total-cobrado');
        const pendienteEl = document.getElementById('stat-total-pendiente');
        const cantidadEl = document.getElementById('stat-cantidad-cuentas');
        
        if (cobradoEl) cobradoEl.innerText = formatCOP(totalCobrado);
        if (pendienteEl) pendienteEl.innerText = formatCOP(totalPendiente);
        if (cantidadEl) cantidadEl.innerText = cuentas.length;
    }

    function filterTable(query) {
        if (!query) {
            renderTable(dbCuentas);
            return;
        }
        const filtered = dbCuentas.filter(c => 
            c.deudor_nombre.toLowerCase().includes(query) || 
            c.deudor_nit.toLowerCase().includes(query) || 
            c.acreedor_nombre.toLowerCase().includes(query) || 
            c.numero_cuenta.toString().includes(query) ||
            c.concepto.toLowerCase().includes(query)
        );
        renderTable(filtered);
    }

    // ----------------------------------------------------
    // General Helpers
    // ----------------------------------------------------
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        if (!toast) return;
        
        toast.innerText = message;
        toast.className = 'toast active ' + type;
        
        setTimeout(() => {
            toast.classList.remove('active');
        }, 4000);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            // YYYY-MM-DD to DD/MM/YYYY
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return dateStr;
    }

    function renderClientSelect(clientes) {
        const select = document.getElementById('select-deudor');
        if (!select) return;
        select.innerHTML = '<option value="">-- Escribir manualmente o seleccionar cliente --</option>';
        clientes.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.innerText = `${c.nombre} (${c.nit})`;
            select.appendChild(opt);
        });
    }

    function renderClientList(clientes) {
        const tbody = document.getElementById('table-clientes-body');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (clientes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:var(--text-muted); padding:1rem;">No hay clientes registrados.</td></tr>';
            return;
        }
        clientes.forEach(c => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding:0.6rem 0.5rem;"><strong>${escapeHtml(c.nombre)}</strong></td>
                <td style="padding:0.6rem 0.5rem; color:var(--text-secondary);">${escapeHtml(c.nit)}</td>
                <td style="padding:0.6rem 0.5rem; text-align:right;">
                    <button type="button" onclick="editCliente(${c.id})" class="btn btn-secondary btn-sm" style="padding:0.25rem 0.5rem; font-size:0.75rem;">Editar</button>
                    <button type="button" onclick="deleteCliente(${c.id})" class="btn btn-danger btn-sm" style="padding:0.25rem 0.5rem; font-size:0.75rem;">Borrar</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function openClientesModal() {
        const modal = document.getElementById('modal-clientes');
        document.getElementById('cliente-id').value = '';
        document.getElementById('cliente-nombre').value = '';
        document.getElementById('cliente-nit').value = '';
        document.getElementById('form-cliente-title').innerText = 'Registrar Nuevo Cliente';
        document.getElementById('btn-cancelar-cliente').style.display = 'none';
        modal.classList.add('active');
    }

    function saveCliente() {
        const id = document.getElementById('cliente-id').value;
        const nombre = document.getElementById('cliente-nombre').value;
        const nit = document.getElementById('cliente-nit').value;

        if (!nombre || !nit) {
            showToast('El nombre y el NIT/Cédula son obligatorios.', 'danger');
            return;
        }

        const payload = {
            id: id || null,
            nombre: nombre,
            nit: nit
        };

        requestAPI('save.php?action=save_cliente', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => {
            if (res.ok && res.data && res.data.status === 'success') {
                showToast(id ? 'Cliente actualizado con éxito.' : 'Cliente registrado con éxito.', 'success');
                document.getElementById('cliente-id').value = '';
                document.getElementById('cliente-nombre').value = '';
                document.getElementById('cliente-nit').value = '';
                document.getElementById('form-cliente-title').innerText = 'Registrar Nuevo Cliente';
                document.getElementById('btn-cancelar-cliente').style.display = 'none';
                loadDashboardData();
            } else {
                const errMsg = res.text ? res.text.replace(/<[^>]*>/g, '').trim().substring(0, 150) : (res.data ? res.data.message : 'Error desconocido');
                showToast('Error al guardar cliente: ' + errMsg, 'danger');
            }
        });
    }

    window.editCliente = function(id) {
        const client = dbClientes.find(c => c.id == id);
        if (client) {
            document.getElementById('cliente-id').value = client.id;
            document.getElementById('cliente-nombre').value = client.nombre;
            document.getElementById('cliente-nit').value = client.nit;
            document.getElementById('form-cliente-title').innerText = 'Editar Cliente';
            document.getElementById('btn-cancelar-cliente').style.display = 'inline-block';
        }
    };

    window.deleteCliente = function(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
            requestAPI('save.php?action=delete_cliente', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(res => {
                if (res.ok && res.data && res.data.status === 'success') {
                    showToast('Cliente eliminado con éxito.', 'success');
                    loadDashboardData();
                } else {
                    const errMsg = res.text ? res.text.replace(/<[^>]*>/g, '').trim().substring(0, 150) : (res.data ? res.data.message : 'Error desconocido');
                    showToast('Error al eliminar cliente: ' + errMsg, 'danger');
                }
            });
        }
    };

    // ----------------------------------------------------
    // CONTROL FINANCIERO Y GASTOS (NUEVA LÓGICA)
    // ----------------------------------------------------
    
    // Configurar filtros de fecha con el mes y año actual
    const todayDate = new Date();
    const currentMonthStr = String(todayDate.getMonth() + 1).padStart(2, '0');
    const currentYearStr = String(todayDate.getFullYear());
    
    const filtroMes = document.getElementById('filtro-mes');
    const filtroAnio = document.getElementById('filtro-anio');
    if (filtroMes) filtroMes.value = currentMonthStr;
    if (filtroAnio) filtroAnio.value = currentYearStr;

    // Tabs navigation logic
    const navCuentas = document.getElementById('nav-cuentas');
    const navFinanzas = document.getElementById('nav-finanzas');
    const sectionCuentas = document.getElementById('section-cuentas');
    const sectionFinanzas = document.getElementById('section-finanzas');

    if (navCuentas && navFinanzas) {
        navCuentas.addEventListener('click', () => {
            navCuentas.classList.add('active');
            navFinanzas.classList.remove('active');
            sectionCuentas.style.display = 'block';
            sectionFinanzas.style.display = 'none';
        });

        navFinanzas.addEventListener('click', () => {
            navFinanzas.classList.add('active');
            navCuentas.classList.remove('active');
            sectionCuentas.style.display = 'none';
            sectionFinanzas.style.display = 'block';
            renderFinanceTables();
            updateFinanceStats();
        });
    }

    // Filtros de mes/año cambio
    if (filtroMes) {
        filtroMes.addEventListener('change', () => {
            renderFinanceTables();
            updateFinanceStats();
        });
    }
    if (filtroAnio) {
        filtroAnio.addEventListener('change', () => {
            renderFinanceTables();
            updateFinanceStats();
        });
    }

    // Buscador de gastos
    const searchGastos = document.getElementById('search-gastos');
    if (searchGastos) {
        searchGastos.addEventListener('input', () => {
            renderFinanceTables();
        });
    }

    // Filtros por columna de gastos
    const filterGastoIds = ['filter-gasto-fecha', 'filter-gasto-categoria', 'filter-gasto-concepto', 'filter-gasto-valor', 'filter-gasto-ejecutado'];
    filterGastoIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            const evName = el.tagName === 'SELECT' ? 'change' : 'input';
            el.addEventListener(evName, () => {
                renderFinanceTables();
            });
        }
    });

    const btnClearGastoFilters = document.getElementById('btn-clear-gasto-filters');
    if (btnClearGastoFilters) {
        btnClearGastoFilters.addEventListener('click', () => {
            filterGastoIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            renderFinanceTables();
        });
    }

    // Toggle de filtros en móvil
    const btnToggleGastoFilters = document.getElementById('btn-toggle-gasto-filters');
    const filterRow = document.querySelector('.filter-row');
    if (btnToggleGastoFilters && filterRow) {
        btnToggleGastoFilters.addEventListener('click', () => {
            const isExpanded = filterRow.classList.toggle('expanded');
            btnToggleGastoFilters.classList.toggle('active', isExpanded);
        });
    }

    // Botón Exportar Excel
    const btnExportarExcel = document.getElementById('btn-exportar-excel');
    if (btnExportarExcel) {
        btnExportarExcel.addEventListener('click', () => {
            exportFinanceToExcel();
        });
    }

    // Modales de Gastos e Ingresos
    const btnNuevoGasto = document.getElementById('btn-nuevo-gasto');
    const btnNuevoIngreso = document.getElementById('btn-nuevo-ingreso');
    const modalGasto = document.getElementById('modal-gasto');
    const modalIngreso = document.getElementById('modal-ingreso');

    if (btnNuevoGasto) {
        btnNuevoGasto.addEventListener('click', () => {
            document.getElementById('form-gasto').reset();
            document.getElementById('gasto-id').value = '';
            document.getElementById('gasto-ejecutado').value = '0';
            document.getElementById('modal-gasto-title').innerText = 'Registrar Gasto';
            
            // Auto fill date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('gasto-fecha').value = today;
            
            modalGasto.classList.add('active');
        });
    }

    if (btnNuevoIngreso) {
        btnNuevoIngreso.addEventListener('click', () => {
            document.getElementById('form-ingreso').reset();
            document.getElementById('ingreso-id').value = '';
            document.getElementById('modal-ingreso-title').innerText = 'Registrar Otro Ingreso';
            
            // Auto fill date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('ingreso-fecha').value = today;
            
            modalIngreso.classList.add('active');
        });
    }

    // Submit forms
    const formGasto = document.getElementById('form-gasto');
    if (formGasto) {
        formGasto.addEventListener('submit', (e) => {
            e.preventDefault();
            saveGasto();
        });
    }

    const formIngreso = document.getElementById('form-ingreso');
    if (formIngreso) {
        formIngreso.addEventListener('submit', (e) => {
            e.preventDefault();
            saveIngreso();
        });
    }

    // CRUD de Gastos
    function saveGasto() {
        const id = document.getElementById('gasto-id').value;
        const fecha = document.getElementById('gasto-fecha').value;
        const categoria = document.getElementById('gasto-categoria').value;
        const concepto = document.getElementById('gasto-concepto').value;
        const valor = document.getElementById('gasto-valor').value;
        const ejecutado = document.getElementById('gasto-ejecutado').value;

        if (!fecha || !categoria || !concepto || !valor) {
            showToast('Por favor, rellene todos los campos.', 'danger');
            return;
        }

        const payload = {
            id: id || null,
            fecha: fecha,
            categoria: categoria,
            concepto: concepto,
            valor: valor,
            ejecutado: ejecutado
        };

        requestAPI('save.php?action=save_gasto', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => {
            if (res.ok && res.data && res.data.status === 'success') {
                showToast(id ? 'Gasto actualizado con éxito.' : 'Gasto registrado con éxito.', 'success');
                modalGasto.classList.remove('active');
                loadDashboardData();
            } else {
                const errMsg = res.text ? res.text.replace(/<[^>]*>/g, '').trim().substring(0, 150) : 'Error';
                showToast('Error al guardar gasto: ' + errMsg, 'danger');
            }
        });
    }

    window.editGasto = function(id) {
        const gasto = dbGastos.find(g => g.id == id);
        if (gasto) {
            document.getElementById('gasto-id').value = gasto.id;
            document.getElementById('gasto-fecha').value = gasto.fecha;
            document.getElementById('gasto-categoria').value = gasto.categoria;
            document.getElementById('gasto-concepto').value = gasto.concepto;
            document.getElementById('gasto-valor').value = gasto.valor;
            document.getElementById('gasto-ejecutado').value = gasto.ejecutado || '0';
            document.getElementById('modal-gasto-title').innerText = 'Editar Gasto';
            modalGasto.classList.add('active');
        }
    };

    window.deleteGasto = function(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este gasto?')) {
            requestAPI('save.php?action=delete_gasto', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(res => {
                if (res.ok && res.data && res.data.status === 'success') {
                    showToast('Gasto eliminado con éxito.', 'success');
                    loadDashboardData();
                } else {
                    showToast('Error al eliminar el gasto.', 'danger');
                }
            });
        }
    };

    window.toggleGastoEjecutado = function(id, currentStatus) {
        const nextStatus = parseInt(currentStatus) === 1 ? 0 : 1;
        requestAPI('save.php?action=toggle_gasto_ejecutado', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, ejecutado: nextStatus })
        })
        .then(res => {
            if (res.ok && res.data && res.data.status === 'success') {
                showToast(nextStatus === 1 ? 'Gasto marcado como Ejecutado.' : 'Gasto marcado como Pendiente.', 'success');
                loadDashboardData();
            } else {
                const errMsg = res.text ? res.text.replace(/<[^>]*>/g, '').trim().substring(0, 150) : (res.data ? res.data.message : 'Error desconocido');
                showToast('Error al cambiar estado del gasto: ' + errMsg, 'danger');
            }
        });
    };

    // CRUD de Otros Ingresos
    function saveIngreso() {
        const id = document.getElementById('ingreso-id').value;
        const fecha = document.getElementById('ingreso-fecha').value;
        const categoria = document.getElementById('ingreso-categoria').value;
        const concepto = document.getElementById('ingreso-concepto').value;
        const valor = document.getElementById('ingreso-valor').value;

        if (!fecha || !categoria || !concepto || !valor) {
            showToast('Por favor, rellene todos los campos.', 'danger');
            return;
        }

        const payload = {
            id: id || null,
            fecha: fecha,
            categoria: categoria,
            concepto: concepto,
            valor: valor
        };

        requestAPI('save.php?action=save_ingreso', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => {
            if (res.ok && res.data && res.data.status === 'success') {
                showToast(id ? 'Ingreso actualizado con éxito.' : 'Ingreso registrado con éxito.', 'success');
                modalIngreso.classList.remove('active');
                loadDashboardData();
            } else {
                const errMsg = res.text ? res.text.replace(/<[^>]*>/g, '').trim().substring(0, 150) : 'Error';
                showToast('Error al guardar ingreso: ' + errMsg, 'danger');
            }
        });
    }

    window.editIngreso = function(id) {
        const ingreso = dbOtrosIngresos.find(i => i.id == id);
        if (ingreso) {
            document.getElementById('ingreso-id').value = ingreso.id;
            document.getElementById('ingreso-fecha').value = ingreso.fecha;
            document.getElementById('ingreso-categoria').value = ingreso.categoria;
            document.getElementById('ingreso-concepto').value = ingreso.concepto;
            document.getElementById('ingreso-valor').value = ingreso.valor;
            document.getElementById('modal-ingreso-title').innerText = 'Editar Otro Ingreso';
            modalIngreso.classList.add('active');
        }
    };

    window.deleteIngreso = function(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este ingreso?')) {
            requestAPI('save.php?action=delete_ingreso', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(res => {
                if (res.ok && res.data && res.data.status === 'success') {
                    showToast('Ingreso eliminado con éxito.', 'success');
                    loadDashboardData();
                } else {
                    showToast('Error al eliminar el ingreso.', 'danger');
                }
            });
        }
    };

    // Render tables functions
    function renderFinanceTables() {
        const mes = filtroMes ? filtroMes.value : currentMonthStr;
        const anio = filtroAnio ? filtroAnio.value : currentYearStr;
        const prefix = `${anio}-${mes}`;
        
        // --- 1. RENDER GASTOS ---
        const tbodyGastos = document.getElementById('table-gastos-body');
        if (tbodyGastos) {
            tbodyGastos.innerHTML = '';
            
            // Filter by month/year prefix (YYYY-MM)
            let filteredGastos = dbGastos.filter(g => g.fecha.startsWith(prefix));
            
            // Apply column-level filters
            const fFecha = document.getElementById('filter-gasto-fecha') ? document.getElementById('filter-gasto-fecha').value.toLowerCase().trim() : '';
            const fCategoria = document.getElementById('filter-gasto-categoria') ? document.getElementById('filter-gasto-categoria').value : '';
            const fConcepto = document.getElementById('filter-gasto-concepto') ? document.getElementById('filter-gasto-concepto').value.toLowerCase().trim() : '';
            const fValor = document.getElementById('filter-gasto-valor') ? document.getElementById('filter-gasto-valor').value.toLowerCase().trim() : '';
            const fEjecutado = document.getElementById('filter-gasto-ejecutado') ? document.getElementById('filter-gasto-ejecutado').value : '';

            if (fFecha) {
                filteredGastos = filteredGastos.filter(g => formatDate(g.fecha).toLowerCase().includes(fFecha));
            }
            if (fCategoria) {
                filteredGastos = filteredGastos.filter(g => g.categoria === fCategoria);
            }
            if (fConcepto) {
                filteredGastos = filteredGastos.filter(g => g.concepto.toLowerCase().includes(fConcepto));
            }
            if (fValor) {
                filteredGastos = filteredGastos.filter(g => 
                    g.valor.toString().toLowerCase().includes(fValor) || 
                    formatCOP(g.valor).toLowerCase().includes(fValor)
                );
            }
            if (fEjecutado !== '') {
                filteredGastos = filteredGastos.filter(g => parseInt(g.ejecutado) === parseInt(fEjecutado));
            }
            
            // Search filter if typed
            const q = searchGastos ? searchGastos.value.toLowerCase().trim() : '';
            if (q) {
                filteredGastos = filteredGastos.filter(g => 
                    g.concepto.toLowerCase().includes(q) || 
                    g.categoria.toLowerCase().includes(q)
                );
            }
            
            if (filteredGastos.length === 0) {
                tbodyGastos.innerHTML = '<tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:1.5rem;">No hay gastos registrados en este mes.</td></tr>';
            } else {
                filteredGastos.forEach(g => {
                    const isEjecutado = parseInt(g.ejecutado) === 1;
                    const statusBadge = isEjecutado 
                        ? `<span class="badge badge-success">Ejecutado</span>` 
                        : `<span class="badge badge-warning">Pendiente</span>`;
                    
                    const toggleTitle = isEjecutado ? 'Marcar como Pendiente' : 'Marcar como Ejecutado';
                    const toggleColor = isEjecutado ? '#10b981' : 'var(--text-secondary)';

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${formatDate(g.fecha)}</td>
                        <td><span class="badge" style="background: rgba(148,163,184,0.1); color: var(--text-primary); font-weight: 500;">${escapeHtml(g.categoria)}</span></td>
                        <td style="font-weight: 500;">${escapeHtml(g.concepto)}</td>
                        <td style="font-weight: 600; color: var(--danger);">${formatCOP(g.valor)}</td>
                        <td>${statusBadge}</td>
                        <td style="text-align: right;">
                            <div class="actions-cell" style="justify-content: flex-end;">
                                <button onclick="toggleGastoEjecutado(${g.id}, ${g.ejecutado})" class="btn btn-secondary btn-sm" title="${toggleTitle}" style="color: ${toggleColor};">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <button onclick="editGasto(${g.id})" class="btn btn-secondary btn-sm" title="Editar" style="padding:0.25rem 0.5rem; font-size:0.75rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button onclick="deleteGasto(${g.id})" class="btn btn-danger btn-sm" title="Eliminar" style="padding:0.25rem 0.5rem; font-size:0.75rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    `;
                    tbodyGastos.appendChild(tr);
                });
            }
        }

        // --- 2. RENDER OTROS INGRESOS ---
        const tbodyOtros = document.getElementById('table-otros-ingresos-body');
        if (tbodyOtros) {
            tbodyOtros.innerHTML = '';
            
            let filteredOtros = dbOtrosIngresos.filter(i => i.fecha.startsWith(prefix));
            
            if (filteredOtros.length === 0) {
                tbodyOtros.innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--text-muted); padding:1.5rem;">No hay otros ingresos registrados.</td></tr>';
            } else {
                filteredOtros.forEach(i => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${formatDate(i.fecha)}</td>
                        <td><span class="badge" style="background: rgba(37,99,235,0.08); color: var(--accent-blue); font-weight: 500;">${escapeHtml(i.categoria)}</span></td>
                        <td style="font-weight: 500;">${escapeHtml(i.concepto)}</td>
                        <td style="font-weight: 600; color: var(--success);">${formatCOP(i.valor)}</td>
                        <td style="text-align: right;">
                            <div class="actions-cell" style="justify-content: flex-end;">
                                <button onclick="editIngreso(${i.id})" class="btn btn-secondary btn-sm" title="Editar" style="padding:0.25rem 0.5rem; font-size:0.75rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button onclick="deleteIngreso(${i.id})" class="btn btn-danger btn-sm" title="Eliminar" style="padding:0.25rem 0.5rem; font-size:0.75rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    `;
                    tbodyOtros.appendChild(tr);
                });
            }
        }

        // --- 3. RENDER CUENTAS DE COBRO DEL MES (COBRADAS) ---
        const tbodyCuentas = document.getElementById('table-cuentas-mes-body');
        if (tbodyCuentas) {
            tbodyCuentas.innerHTML = '';
            
            // Filter accounts of this month that are PAID
            let filteredCuentas = dbCuentas.filter(c => c.fecha.startsWith(prefix) && parseInt(c.pagado) === 1);
            
            if (filteredCuentas.length === 0) {
                tbodyCuentas.innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:1.5rem;">No hay cuentas de cobro pagadas en este mes.</td></tr>';
            } else {
                filteredCuentas.forEach(c => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong># ${c.numero_cuenta}</strong></td>
                        <td style="font-weight: 500;">${escapeHtml(c.deudor_nombre)}</td>
                        <td>${formatDate(c.fecha)}</td>
                        <td style="font-weight: 600; color: var(--success);">${formatCOP(c.valor)}</td>
                    `;
                    tbodyCuentas.appendChild(tr);
                });
            }
        }
    }

    function updateFinanceStats() {
        const mes = filtroMes ? filtroMes.value : currentMonthStr;
        const anio = filtroAnio ? filtroAnio.value : currentYearStr;
        const prefix = `${anio}-${mes}`;
        
        // Sum incomes
        const sumCuentasPagadas = dbCuentas.reduce((sum, c) => {
            if (c.fecha.startsWith(prefix) && parseInt(c.pagado) === 1) {
                return sum + parseFloat(c.valor);
            }
            return sum;
        }, 0);

        const sumCuentasPendientes = dbCuentas.reduce((sum, c) => {
            if (c.fecha.startsWith(prefix) && parseInt(c.pagado) !== 1) {
                return sum + parseFloat(c.valor);
            }
            return sum;
        }, 0);

        const sumOtrosIngresos = dbOtrosIngresos.reduce((sum, i) => {
            if (i.fecha.startsWith(prefix)) {
                return sum + parseFloat(i.valor);
            }
            return sum;
        }, 0);

        const totalIngresos = sumCuentasPagadas + sumOtrosIngresos;

        // Sum expenses
        const sumGastos = dbGastos.reduce((sum, g) => {
            if (g.fecha.startsWith(prefix)) {
                return sum + parseFloat(g.valor);
            }
            return sum;
        }, 0);

        const sumGastosEjecutados = dbGastos.reduce((sum, g) => {
            if (g.fecha.startsWith(prefix) && parseInt(g.ejecutado) === 1) {
                return sum + parseFloat(g.valor);
            }
            return sum;
        }, 0);

        const sumGastosPendientes = dbGastos.reduce((sum, g) => {
            if (g.fecha.startsWith(prefix) && parseInt(g.ejecutado) !== 1) {
                return sum + parseFloat(g.valor);
            }
            return sum;
        }, 0);

        const numGastos = dbGastos.filter(g => g.fecha.startsWith(prefix)).length;

        // Calculate balance (only subtract executed expenses)
        const balanceNeto = totalIngresos - sumGastosEjecutados;

        // Update DOM
        const ingresosEl = document.getElementById('stat-finanzas-ingresos');
        const gastosEl = document.getElementById('stat-finanzas-gastos');
        const balanceEl = document.getElementById('stat-finanzas-balance');
        
        if (ingresosEl) ingresosEl.innerText = formatCOP(totalIngresos);
        if (gastosEl) gastosEl.innerText = formatCOP(sumGastosEjecutados);
        if (balanceEl) balanceEl.innerText = formatCOP(balanceNeto);

        const breakdownIngresos = document.getElementById('breakdown-ingresos');
        if (breakdownIngresos) {
            breakdownIngresos.innerText = `Cuentas: ${formatCOP(sumCuentasPagadas)} | Otros: ${formatCOP(sumOtrosIngresos)}`;
        }

        const breakdownGastos = document.getElementById('breakdown-gastos');
        if (breakdownGastos) {
            breakdownGastos.innerText = `Ejecutado: ${formatCOP(sumGastosEjecutados)} | Pendiente: ${formatCOP(sumGastosPendientes)}`;
        }

        const breakdownBalance = document.getElementById('breakdown-balance');
        const cardBalance = document.getElementById('card-balance');
        
        if (cardBalance) {
            if (balanceNeto >= 0) {
                cardBalance.className = 'card balance-positive';
                if (breakdownBalance) {
                    if (sumGastosPendientes > 0) {
                        const balanceProyectado = balanceNeto - sumGastosPendientes;
                        breakdownBalance.innerText = `Superávit real | Proyectado: ${formatCOP(balanceProyectado)}`;
                    } else {
                        breakdownBalance.innerText = 'Superávit / Ahorro positivo';
                    }
                }
            } else {
                cardBalance.className = 'card balance-negative';
                if (breakdownBalance) {
                    if (sumGastosPendientes > 0) {
                        const balanceProyectado = balanceNeto - sumGastosPendientes;
                        breakdownBalance.innerText = `Déficit real | Proyectado: ${formatCOP(balanceProyectado)}`;
                    } else {
                        breakdownBalance.innerText = 'Déficit / Gasto excede ingresos';
                    }
                }
            }
        }

        const lblPendientes = document.getElementById('lbl-cuentas-pendientes');
        if (lblPendientes) {
            lblPendientes.innerText = `Pendientes en mes: ${formatCOP(sumCuentasPendientes)}`;
        }
    }

    function exportFinanceToExcel() {
        const mes = filtroMes ? filtroMes.value : currentMonthStr;
        const anio = filtroAnio ? filtroAnio.value : currentYearStr;
        const prefix = `${anio}-${mes}`;
        
        // Month names in Spanish
        const mesesNombres = {
            "01": "Enero", "02": "Febrero", "03": "Marzo", "04": "Abril", "05": "Mayo", "06": "Junio",
            "07": "Julio", "08": "Agosto", "09": "Septiembre", "10": "Octubre", "11": "Noviembre", "12": "Diciembre"
        };
        const nombreMes = mesesNombres[mes] || mes;

        // Filter data
        const filteredGastos = dbGastos.filter(g => g.fecha.startsWith(prefix));
        const filteredOtros = dbOtrosIngresos.filter(i => i.fecha.startsWith(prefix));
        const filteredCuentas = dbCuentas.filter(c => c.fecha.startsWith(prefix) && parseInt(c.pagado) === 1);

        // Sum calculations
        const sumCuentasPagadas = filteredCuentas.reduce((sum, c) => sum + parseFloat(c.valor), 0);
        const sumOtrosIngresos = filteredOtros.reduce((sum, i) => sum + parseFloat(i.valor), 0);
        const totalIngresos = sumCuentasPagadas + sumOtrosIngresos;
        
        const sumGastosEjecutados = filteredGastos.reduce((sum, g) => {
            if (parseInt(g.ejecutado) === 1) {
                return sum + parseFloat(g.valor);
            }
            return sum;
        }, 0);
        const sumGastosPendientes = filteredGastos.reduce((sum, g) => {
            if (parseInt(g.ejecutado) !== 1) {
                return sum + parseFloat(g.valor);
            }
            return sum;
        }, 0);
        const sumTotalGastos = sumGastosEjecutados + sumGastosPendientes;
        const balanceNeto = totalIngresos - sumGastosEjecutados;

        // Construct CSV
        let csvContent = "\uFEFF"; // UTF-8 BOM for Spanish character encoding in Excel

        // 1. Resumen Section
        csvContent += `RESUMEN FINANCIERO MENSUAL;;;;\n`;
        csvContent += `Periodo;${nombreMes} ${anio};;;;\n`;
        csvContent += `Ingresos Totales;${totalIngresos};;;; (Cuentas Cobradas + Otros Ingresos)\n`;
        csvContent += `Gastos Ejecutados;${sumGastosEjecutados};;;;\n`;
        csvContent += `Gastos Pendientes;${sumGastosPendientes};;;;\n`;
        csvContent += `Balance Neto (Real);${balanceNeto};;;; (${balanceNeto >= 0 ? 'Superávit' : 'Déficit'} - Basado en Gastos Ejecutados)\n`;
        csvContent += `;;;\n`;

        // 2. Gastos Section
        csvContent += `LISTADO DE GASTOS;;;;\n`;
        csvContent += `Fecha;Categoría;Concepto;Valor;Estado\n`;
        if (filteredGastos.length === 0) {
            csvContent += `No hay gastos registrados en este mes;;;;\n`;
        } else {
            filteredGastos.forEach(g => {
                const estadoTxt = parseInt(g.ejecutado) === 1 ? 'Ejecutado' : 'Pendiente';
                csvContent += `${g.fecha};"${g.categoria.replace(/"/g, '""')}";"${g.concepto.replace(/"/g, '""')}";${g.valor};"${estadoTxt}"\n`;
            });
        }
        csvContent += `;;;\n`;

        // 3. Otros Ingresos Section
        csvContent += `OTROS INGRESOS (NO CUENTAS);;;\n`;
        csvContent += `Fecha;Origen / Categoría;Concepto;Valor\n`;
        if (filteredOtros.length === 0) {
            csvContent += `No hay otros ingresos registrados;;;\n`;
        } else {
            filteredOtros.forEach(i => {
                csvContent += `${i.fecha};"${i.categoria.replace(/"/g, '""')}";"${i.concepto.replace(/"/g, '""')}";${i.valor}\n`;
            });
        }
        csvContent += `;;;\n`;

        // 4. Cuentas de Cobro Pagadas Section
        csvContent += `CUENTAS DE COBRO COBRADAS;;;\n`;
        csvContent += `N° Cuenta;Deudor / Cliente;Fecha;Valor\n`;
        if (filteredCuentas.length === 0) {
            csvContent += `No hay cuentas cobradas en este mes;;;\n`;
        } else {
            filteredCuentas.forEach(c => {
                csvContent += `${c.numero_cuenta};"${c.deudor_nombre.replace(/"/g, '""')}";${c.fecha};${c.valor}\n`;
            });
        }

        // Trigger Download
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", `Control_Financiero_${nombreMes}_${anio}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast('Archivo Excel exportado con éxito.', 'success');
        } else {
            showToast('Tu navegador no soporta descargas directas.', 'danger');
        }
    }
});
