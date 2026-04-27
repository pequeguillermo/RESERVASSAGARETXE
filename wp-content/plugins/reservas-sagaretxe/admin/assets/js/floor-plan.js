document.addEventListener('DOMContentLoaded', function() {
    
    // Configurar el grid y las mesas arrastrables
    interact('.rrm-table')
        .draggable({
            inertia: true,
            modifiers: [
                interact.modifiers.restrictRect({
                    restriction: 'parent',
                    endOnly: true
                })
            ],
            autoScroll: true,
            listeners: {
                move: dragMoveListener,
            }
        })
        .resizable({
            edges: { left: true, right: true, bottom: true, top: true },
            modifiers: [
                interact.modifiers.restrictEdges({
                    outer: 'parent'
                }),
                interact.modifiers.restrictSize({
                    min: { width: 50, height: 50 }
                })
            ],
            inertia: true
        })
        .on('resizemove', function (event) {
            var target = event.target;
            var x = (parseFloat(target.getAttribute('data-x')) || 0);
            var y = (parseFloat(target.getAttribute('data-y')) || 0);

            target.style.width = event.rect.width + 'px';
            target.style.height = event.rect.height + 'px';

            x += event.deltaRect.left;
            y += event.deltaRect.top;

            target.style.transform = 'translate(' + x + 'px,' + y + 'px)';

            target.setAttribute('data-x', x);
            target.setAttribute('data-y', y);
        });

    function dragMoveListener (event) {
        var target = event.target;
        var x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
        var y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

        target.style.transform = 'translate(' + x + 'px, ' + y + 'px)';
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    }

    // Configurar mesas como zonas donde se pueden soltar reservas
    interact('.rrm-table').dropzone({
        accept: '.rrm-draggable-res',
        overlap: 0.50,
        ondragenter: function (event) {
            var draggableElement = event.relatedTarget;
            var dropzoneElement = event.target;
            dropzoneElement.classList.add('drop-target');
        },
        ondragleave: function (event) {
            event.target.classList.remove('drop-target');
        },
        ondrop: function (event) {
            var resEl = event.relatedTarget;
            var tableEl = event.target;
            
            tableEl.classList.remove('drop-target');
            
            var resId = resEl.getAttribute('data-res-id');
            var tableId = tableEl.getAttribute('data-table-id');
            
            if (tableId && resId) {
                // Hacer AJAX request para asignar mesa
                var formData = new URLSearchParams();
                formData.append('action', 'rrm_assign_table');
                formData.append('reservation_id', resId);
                formData.append('table_id', tableId);

                fetch(rrm_admin_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        resEl.style.display = 'none'; // Quitar de la lista
                        var name = resEl.querySelector('strong').innerText;
                        tableEl.querySelector('.rrm-table-reservations').innerHTML += `<div>${name}</div>`;
                    } else {
                        alert('Error al asignar mesa.');
                    }
                });
            }
        }
    });

    // Configurar reservas de la lista lateral como arrastrables
    interact('.rrm-draggable-res').draggable({
        inertia: true,
        autoScroll: true,
        onstart: function(event) {
            event.target.style.position = 'absolute';
            event.target.style.zIndex = 1000;
        },
        onmove: dragMoveListener,
        onend: function(event) {
            // Si no se soltó en un dropzone válido, devolver al origen
            if(!event.dropzone) {
                event.target.style.transform = 'translate(0px, 0px)';
                event.target.setAttribute('data-x', 0);
                event.target.setAttribute('data-y', 0);
                event.target.style.position = 'relative';
                event.target.style.zIndex = 'auto';
            }
        }
    });

    // Botón Guardar Disposición
    document.getElementById('btn-save-layout').addEventListener('click', function() {
        var tables = document.querySelectorAll('.rrm-table');
        var layout = [];
        tables.forEach(function(t) {
            layout.push({
                id: t.getAttribute('data-table-id'),
                x: t.getAttribute('data-x'),
                y: t.getAttribute('data-y'),
                w: t.offsetWidth,
                h: t.offsetHeight
            });
        });

        // Enviar layout actualizado a base de datos
        var formData = new URLSearchParams();
        formData.append('action', 'rrm_save_layout');
        layout.forEach((l, index) => {
            formData.append(`layout[${index}][id]`, l.id);
            formData.append(`layout[${index}][x]`, l.x);
            formData.append(`layout[${index}][y]`, l.y);
            formData.append(`layout[${index}][w]`, l.w);
            formData.append(`layout[${index}][h]`, l.h);
        });

        fetch(rrm_admin_ajax.ajax_url, {
            method: 'POST',
            body: formData
        }).then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Disposición de sala guardada con éxito.');
            }
        });
    });

    var modal = document.getElementById('rrm-table-modal');
    var inputName = document.getElementById('rrm-modal-name');
    var inputCap = document.getElementById('rrm-modal-cap');

    var currentEditingTable = null;

    // Mostrar modal nueva mesa
    document.getElementById('btn-add-table').addEventListener('click', function() {
        currentEditingTable = null;
        document.getElementById('rrm-modal-title').innerText = 'Añadir Nueva Mesa';
        document.getElementById('btn-confirm-table').innerText = 'Añadir Mesa';
        document.getElementById('btn-delete-table').style.display = 'none';
        
        inputName.value = '';
        inputCap.value = '2';
        document.querySelector('input[name="rrm-modal-forma"][value="rect"]').checked = true;
        modal.style.display = 'block';
    });

    // Editar mesa (doble click)
    document.getElementById('rrm-floor-canvas').addEventListener('dblclick', function(e) {
        var table = e.target.closest('.rrm-table');
        if(!table) return;

        currentEditingTable = table;
        document.getElementById('rrm-modal-title').innerText = 'Editar Mesa';
        document.getElementById('btn-confirm-table').innerText = 'Guardar Cambios';
        
        var tableId = table.getAttribute('data-table-id');
        if (tableId) {
            document.getElementById('btn-delete-table').style.display = 'inline-block';
        } else {
            // Es una mesa nueva (aún no guardada en BD)
            document.getElementById('btn-delete-table').style.display = 'inline-block';
        }

        inputName.value = table.querySelector('.table-name').innerText;
        var capText = table.querySelector('.table-cap').innerText;
        inputCap.value = capText.replace(/\D/g, ''); // Extraer solo números de "(Cap: X)"
        
        var forma = table.classList.contains('round') ? 'round' : 'rect';
        document.querySelector('input[name="rrm-modal-forma"][value="' + forma + '"]').checked = true;
        
        modal.style.display = 'block';
    });

    // Ocultar modal
    document.getElementById('btn-cancel-table').addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Confirmar añadir/editar mesa
    document.getElementById('btn-confirm-table').addEventListener('click', function() {
        var name = inputName.value.trim();
        var cap = inputCap.value;
        var forma = document.querySelector('input[name="rrm-modal-forma"]:checked').value;

        if(!name || !cap) {
            alert('El nombre y la capacidad son obligatorios.');
            return;
        }

        modal.style.display = 'none';

        if (currentEditingTable) {
            // Actualizar mesa existente
            currentEditingTable.classList.remove('round', 'rect');
            currentEditingTable.classList.add(forma);
            currentEditingTable.querySelector('.table-name').innerText = name;
            currentEditingTable.querySelector('.table-cap').innerText = '(Cap: ' + cap + ')';
            
            currentEditingTable.setAttribute('data-name', name);
            currentEditingTable.setAttribute('data-cap', cap);
            currentEditingTable.setAttribute('data-forma', forma);
        } else {
            // Crear nueva mesa
            var canvas = document.getElementById('rrm-floor-canvas');
            var newTable = document.createElement('div');
            newTable.className = 'rrm-table draggable resizable ' + forma;
            newTable.setAttribute('data-x', 0);
            newTable.setAttribute('data-y', 0);
            newTable.style.width = '100px';
            newTable.style.height = '100px';
            newTable.innerHTML = `<span class="table-name">${name}</span><span class="table-cap">(Cap: ${cap})</span><div class="rrm-table-reservations"></div>`;
            
            newTable.setAttribute('data-name', name);
            newTable.setAttribute('data-cap', cap);
            newTable.setAttribute('data-forma', forma);

            canvas.appendChild(newTable);
        }
    });

    // Borrar mesa
    document.getElementById('btn-delete-table').addEventListener('click', function() {
        if(!confirm('¿Estás seguro de que deseas borrar esta mesa?')) return;

        if (currentEditingTable) {
            var tableId = currentEditingTable.getAttribute('data-table-id');
            if (tableId) {
                // Borrar de base de datos
                var formData = new URLSearchParams();
                formData.append('action', 'rrm_delete_table');
                formData.append('table_id', tableId);

                fetch(rrm_admin_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                }).then(response => response.json())
                .then(data => {
                    if(data.success) {
                        currentEditingTable.remove();
                        modal.style.display = 'none';
                    } else {
                        alert('Error al borrar la mesa.');
                    }
                });
            } else {
                // Solo borrar del DOM (no estaba en BD)
                currentEditingTable.remove();
                modal.style.display = 'none';
            }
        }
    });
    
    // Update save layout logic to include name, cap and forma for new tables
    document.getElementById('btn-save-layout').addEventListener('click', function(e) {
        e.stopImmediatePropagation(); // Evitar duplicado de event listener
        
        var tables = document.querySelectorAll('.rrm-table');
        var layout = [];
        tables.forEach(function(t) {
            layout.push({
                id: t.getAttribute('data-table-id'),
                name: t.getAttribute('data-name'),
                cap: t.getAttribute('data-cap'),
                forma: t.getAttribute('data-forma'),
                x: t.getAttribute('data-x'),
                y: t.getAttribute('data-y'),
                w: t.offsetWidth,
                h: t.offsetHeight
            });
        });

        var formData = new URLSearchParams();
        formData.append('action', 'rrm_save_layout');
        layout.forEach((l, index) => {
            if (l.id) formData.append(`layout[${index}][id]`, l.id);
            if (l.name) formData.append(`layout[${index}][name]`, l.name);
            if (l.cap) formData.append(`layout[${index}][cap]`, l.cap);
            if (l.forma) formData.append(`layout[${index}][forma]`, l.forma);
            formData.append(`layout[${index}][x]`, l.x);
            formData.append(`layout[${index}][y]`, l.y);
            formData.append(`layout[${index}][w]`, l.w);
            formData.append(`layout[${index}][h]`, l.h);
        });

        fetch(rrm_admin_ajax.ajax_url, {
            method: 'POST',
            body: formData
        }).then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Disposición de sala guardada. (Recarga para actualizar IDs).');
                location.reload();
            }
        });
    });

});
