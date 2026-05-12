document.addEventListener('DOMContentLoaded', function() {
    const btnSubmit = document.getElementById('rrm-btn-submit');
    const messages = document.getElementById('rrm-messages');
    const dateInput = document.getElementById('rrm-date');
    const timeSelect = document.getElementById('rrm-time');

    // Helper para generar opciones de hora (intervalos de 30 min)
    function generateTimeOptions(open, close) {
        if (!open || !close) return [];
        let options = [];
        let [openH, openM] = open.split(':').map(Number);
        let [closeH, closeM] = close.split(':').map(Number);
        
        let current = new Date();
        current.setHours(openH, openM, 0);
        let end = new Date();
        end.setHours(closeH, closeM, 0);
        
        while (current <= end) {
            let h = current.getHours().toString().padStart(2, '0');
            let m = current.getMinutes().toString().padStart(2, '0');
            options.push(`${h}:${m}`);
            current.setMinutes(current.getMinutes() + 30);
        }
        return options;
    }

    if (dateInput) {
        dateInput.addEventListener('change', async function(e) {
            const date = e.target.value;
            messages.innerHTML = '<span style="color:blue;">Comprobando disponibilidad...</span>';
            btnSubmit.disabled = true;
            timeSelect.innerHTML = '<option value="">Cargando horas...</option>';
            timeSelect.disabled = true;

            try {
                const response = await fetch(`https://app.sagaretxe.net/api/schedules/availability?date=${date}`);
                const data = await response.json();

                if (data.available === false) {
                    messages.innerHTML = `<span style="color:red;">El restaurante está cerrado en esta fecha: ${data.message || ''}</span>`;
                    timeSelect.innerHTML = '<option value="">Cerrado</option>';
                } else {
                    let allOptions = [];
                    // Turno 1
                    if (data.open_time && data.close_time) {
                        allOptions = allOptions.concat(generateTimeOptions(data.open_time, data.close_time));
                    }
                    // Turno 2
                    if (data.open_time_2 && data.close_time_2) {
                        allOptions = allOptions.concat(generateTimeOptions(data.open_time_2, data.close_time_2));
                    }

                    if (allOptions.length === 0) {
                        messages.innerHTML = `<span style="color:red;">No hay horas disponibles para este día.</span>`;
                        timeSelect.innerHTML = '<option value="">Sin horas</option>';
                        return;
                    }

                    timeSelect.innerHTML = '<option value="">Selecciona hora</option>';
                    allOptions.forEach(time => {
                        let opt = document.createElement('option');
                        opt.value = time;
                        opt.textContent = time;
                        timeSelect.appendChild(opt);
                    });
                    
                    timeSelect.disabled = false;
                    messages.innerHTML = `<span style="color:green;">Fecha disponible. Selecciona una hora.</span>`;
                    btnSubmit.disabled = false;
                }
            } catch (error) {
                messages.innerHTML = '<span style="color:red;">Error comprobando disponibilidad.</span>';
                console.error(error);
            }
        });
    }

    const form = document.getElementById('rrm-step-1');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const date = document.getElementById('rrm-date').value;
            const time = document.getElementById('rrm-time').value;
            const guests = document.getElementById('rrm-guests').value;
            const name = document.getElementById('rrm-name').value;
            const email = document.getElementById('rrm-email').value;
            const phone = document.getElementById('rrm-phone').value;
            const notes = document.getElementById('rrm-notes') ? document.getElementById('rrm-notes').value : '';

            // Ya no es necesario comprobar manualmente si están vacíos porque HTML5 lo hace por nosotros, 
            // pero lo dejamos como doble validación de seguridad.
            if (!date || !time || !guests || !name || !email || !phone) {
                messages.innerHTML = '<span style="color:red;">Por favor, completa todos los campos requeridos.</span>';
                messages.style.display = 'block';
                return;
            }

            btnSubmit.disabled = true;
            btnSubmit.innerText = 'Procesando...';
            messages.innerHTML = '';
            messages.style.display = 'none';

            // Llama a la API central de Laravel
            fetch('https://app.sagaretxe.net/api/reservations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    date: `${date} ${time}:00`,
                    people: guests,
                    name: name,
                    email: email,
                    phone: phone,
                    notes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                btnSubmit.disabled = false;
                btnSubmit.innerText = 'Confirmar Reserva';
                messages.style.display = 'block';

                if (data.reservation_id) {
                    let successMsg = `<span style="color:green;">¡Reserva confirmada con éxito! Revisa tu email.</span>`;
                    if (data.discount_applied) {
                        successMsg += `<br><span style="color:green;"><strong>¡PERTENECES AL CLUB SAGARETXE! Recuerda indicarlo para que te apliquemos tu descuento.</strong></span>`;
                    }
                    messages.innerHTML = successMsg;
                    document.getElementById('rrm-step-1').style.display = 'none';
                } else if (data.message) {
                    messages.innerHTML = `<span style="color:red;">Error: ${data.message}</span>`;
                } else {
                    messages.innerHTML = `<span style="color:red;">Ha ocurrido un error inesperado.</span>`;
                }
            })
            .catch(error => {
                btnSubmit.disabled = false;
                btnSubmit.innerText = 'Confirmar Reserva';
                messages.style.display = 'block';
                messages.innerHTML = '<span style="color:red;">Error de conexión con el servidor.</span>';
                console.error(error);
            });
        });
    }
});
