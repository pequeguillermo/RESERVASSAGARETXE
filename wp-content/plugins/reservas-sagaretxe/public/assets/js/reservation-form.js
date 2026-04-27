document.addEventListener('DOMContentLoaded', function() {
    const btnCheck = document.getElementById('rrm-btn-check-availability');
    const btnSubmit = document.getElementById('rrm-btn-submit');
    const msgDiv = document.getElementById('rrm-messages');
    const step2 = document.getElementById('rrm-step-2');
    const step3 = document.getElementById('rrm-step-3');
    const shiftsContainer = document.getElementById('rrm-shifts-container');

    if (!btnCheck) return;

    btnCheck.addEventListener('click', function() {
        const date = document.getElementById('rrm-date').value;
        const guests = document.getElementById('rrm-guests').value;

        msgDiv.innerHTML = '';
        if (!date) {
            msgDiv.innerHTML = 'Por favor selecciona una fecha.';
            return;
        }

        btnCheck.disabled = true;
        fetch(`${rrm_ajax.rest_url}rrm/v1/availability?date=${date}&guests=${guests}`)
            .then(response => response.json())
            .then(data => {
                btnCheck.disabled = false;
                if (data.code && data.message) {
                    msgDiv.innerHTML = data.message;
                    return;
                }

                if (data.length === 0) {
                    msgDiv.innerHTML = 'No hay turnos disponibles para esta fecha y comensales.';
                    step2.style.display = 'none';
                    step3.style.display = 'none';
                    return;
                }

                shiftsContainer.innerHTML = '';
                data.forEach(shift => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.innerText = `${shift.nombre} (${shift.hora_inicio} - ${shift.hora_fin}) [Restantes: ${shift.remaining}]`;
                    btn.style.display = 'block';
                    btn.style.marginBottom = '10px';
                    btn.addEventListener('click', function() {
                        document.getElementById('rrm-selected-shift').value = shift.id;
                        step3.style.display = 'block';
                        msgDiv.innerHTML = `Turno seleccionado: ${shift.nombre}`;
                    });
                    shiftsContainer.appendChild(btn);
                });

                step2.style.display = 'block';
                step3.style.display = 'none';
            })
            .catch(error => {
                btnCheck.disabled = false;
                msgDiv.innerHTML = 'Error de conexión.';
            });
    });

    btnSubmit.addEventListener('click', function() {
        const date = document.getElementById('rrm-date').value;
        const guests = document.getElementById('rrm-guests').value;
        const shift_id = document.getElementById('rrm-selected-shift').value;
        const name = document.getElementById('rrm-name').value;
        const email = document.getElementById('rrm-email').value;
        const phone = document.getElementById('rrm-phone').value;

        if (!name || !email || !phone) {
            msgDiv.innerHTML = 'Por favor completa todos los datos.';
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerText = 'Enviando...';

        fetch(`${rrm_ajax.rest_url}rrm/v1/reserve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                date, guests, shift_id, name, email, phone
            })
        })
        .then(response => response.json())
        .then(data => {
            btnSubmit.disabled = false;
            btnSubmit.innerText = 'Confirmar Reserva';

            if (data.success) {
                document.getElementById('rrm-reservation-app').innerHTML = `<h3 style="color: green;">${data.message}</h3>`;
            } else {
                msgDiv.innerHTML = data.message || 'Error al procesar la reserva.';
            }
        })
        .catch(error => {
            btnSubmit.disabled = false;
            btnSubmit.innerText = 'Confirmar Reserva';
            msgDiv.innerHTML = 'Error de conexión.';
        });
    });
});
