<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RRM_Shortcodes {

    public static function init() {
        add_shortcode( 'restaurant_reservations', [ __CLASS__, 'render_reservation_form' ] );
    }

    public static function render_reservation_form() {
        ob_start();
        ?>
        <div id="rrm-reservation-app" class="rrm-container" style="max-width: 600px; margin: 2rem auto; font-family: 'Inter', system-ui, sans-serif; background: #ffffff; padding: 30px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;">
            <h2 style="text-align: center; color: #111827; margin-bottom: 5px; font-weight: 800; font-size: 1.8rem;">Reserva tu mesa</h2>
            <p style="text-align: center; color: #6b7280; font-size: 0.95rem; margin-bottom: 25px;">Completa los datos a continuación y asegura tu lugar.</p>
            
            <style>
                .rrm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
                .rrm-full { grid-column: span 2; }
                .rrm-form-group { display: flex; flex-direction: column; gap: 6px; }
                .rrm-label { font-weight: 600; font-size: 0.85rem; color: #374151; }
                .rrm-input { padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; color: #111827; background: #f9fafb; transition: all 0.2s; outline: none; }
                .rrm-input:focus { border-color: #4f46e5; background: #fff; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
                .rrm-btn { width: 100%; padding: 14px; background-color: #111827; color: white; border: none; border-radius: 8px; font-size: 1.05rem; font-weight: bold; cursor: pointer; transition: background-color 0.2s, transform 0.1s; }
                .rrm-btn:hover { background-color: #374151; }
                .rrm-btn:active { transform: scale(0.98); }
                .rrm-btn:disabled { background-color: #9ca3af; cursor: not-allowed; }
                @media (max-width: 480px) { .rrm-grid { grid-template-columns: 1fr; } .rrm-full { grid-column: span 1; } }
            </style>

            <form id="rrm-step-1" class="rrm-grid">
                <div class="rrm-form-group">
                    <label class="rrm-label">Fecha *</label>
                    <input type="date" id="rrm-date" class="rrm-input" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="rrm-form-group">
                    <label class="rrm-label">Hora *</label>
                    <select id="rrm-time" class="rrm-input" required disabled><option value="">Selecciona fecha primero</option></select>
                </div>
                <div class="rrm-form-group">
                    <label class="rrm-label">Adultos *</label>
                    <input type="number" id="rrm-adults" class="rrm-input" min="1" max="20" value="2" required>
                </div>
                <div class="rrm-form-group">
                    <label class="rrm-label">Niños</label>
                    <input type="number" id="rrm-children" class="rrm-input" min="0" max="10" value="0">
                </div>
                <div class="rrm-full" style="background:#f0f4ff;border:1px solid #c7d2fe;border-radius:8px;padding:10px 15px;display:flex;align-items:center;gap:8px;">
                    <span style="font-size:0.9rem;color:#4338ca;font-weight:600;">👥 Total de comensales:</span>
                    <span id="rrm-total-guests" style="font-size:1.1rem;color:#4338ca;font-weight:800;">2</span>
                </div>
                <div class="rrm-form-group rrm-full">
                    <label class="rrm-label">Nombre Completo *</label>
                    <input type="text" id="rrm-name" class="rrm-input" placeholder="Ej: María García" required>
                </div>
                <div class="rrm-form-group">
                    <label class="rrm-label">Email *</label>
                    <input type="email" id="rrm-email" class="rrm-input" placeholder="tucorreo@ejemplo.com" required>
                </div>
                <div class="rrm-form-group">
                    <label class="rrm-label">Teléfono *</label>
                    <input type="tel" id="rrm-phone" class="rrm-input" placeholder="600 000 000" required>
                </div>

                <!-- Necesidades especiales -->
                <div class="rrm-form-group rrm-full">
                    <label class="rrm-label" style="margin-bottom:8px;">Necesidades especiales</label>
                    <div style="display:flex; flex-wrap:wrap; gap:12px;">
                        <label style="display:flex;align-items:center;gap:6px;font-size:0.9rem;color:#374151;cursor:pointer;">
                            <input type="checkbox" id="rrm-allergies" style="width:16px;height:16px;cursor:pointer;"> Alergias alimentarias
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:0.9rem;color:#374151;cursor:pointer;">
                            <input type="checkbox" id="rrm-celiac" style="width:16px;height:16px;cursor:pointer;"> Celíaco/a
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:0.9rem;color:#374151;cursor:pointer;">
                            <input type="checkbox" id="rrm-strollers" style="width:16px;height:16px;cursor:pointer;"> Carrito de bebé
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:0.9rem;color:#374151;cursor:pointer;">
                            <input type="checkbox" id="rrm-reduced-mobility" style="width:16px;height:16px;cursor:pointer;"> Movilidad reducida
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:0.9rem;color:#374151;cursor:pointer;">
                            <input type="checkbox" id="rrm-wheelchairs" style="width:16px;height:16px;cursor:pointer;"> Silla de ruedas
                        </label>
                    </div>
                </div>

                <div class="rrm-form-group rrm-full">
                    <label class="rrm-label">Notas adicionales (Opcional)</label>
                    <textarea id="rrm-notes" class="rrm-input" rows="3" placeholder="Celebración especial, preferencias de mesa..."></textarea>
                </div>
                <div class="rrm-full" style="margin-top: 10px;">
                    <button type="submit" id="rrm-btn-submit" class="rrm-btn">Confirmar Reserva</button>
                </div>
            </form>

            <div id="rrm-messages" style="margin-top: 20px; font-weight: bold; text-align: center; border-radius: 8px; padding: 10px; display: none;"></div>
        </div>

        <script>
        (function() {
            var API_BASE = 'https://app.sagaretxe.net/api';

            function rrmUpdateTotal() {
                var a = document.getElementById('rrm-adults');
                var c = document.getElementById('rrm-children');
                var t = document.getElementById('rrm-total-guests');
                if (a && c && t) t.textContent = (parseInt(a.value)||0) + (parseInt(c.value)||0);
            }

            document.addEventListener('DOMContentLoaded', function() {
                var btnSubmit = document.getElementById('rrm-btn-submit');
                var messages = document.getElementById('rrm-messages');
                var dateInput = document.getElementById('rrm-date');
                var timeSelect = document.getElementById('rrm-time');
                var adultsInput = document.getElementById('rrm-adults');
                var childrenInput = document.getElementById('rrm-children');

                if (!dateInput || !timeSelect || !btnSubmit || !messages) return;

                if (adultsInput) adultsInput.addEventListener('input', rrmUpdateTotal);
                if (childrenInput) childrenInput.addEventListener('input', rrmUpdateTotal);
                rrmUpdateTotal();

                dateInput.addEventListener('change', function() {
                    var date = dateInput.value;
                    if (!date) return;

                    messages.innerHTML = '<span style="color:blue;">Comprobando disponibilidad...</span>';
                    messages.style.display = 'block';
                    btnSubmit.disabled = true;
                    timeSelect.innerHTML = '<option value="">Cargando horas...</option>';
                    timeSelect.disabled = true;

                    fetch(API_BASE + '/schedules/availability?date=' + date)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.available === false) {
                            messages.innerHTML = '<span style="color:red;">El restaurante está cerrado en esta fecha' + (data.message ? ': ' + data.message : '') + '</span>';
                            timeSelect.innerHTML = '<option value="">Cerrado</option>';
                            return;
                        }

                        var allOptions = [];
                        function addSlots(openStr, closeStr) {
                            if (!openStr || !closeStr) return;
                            var p1 = openStr.split(':'), p2 = closeStr.split(':');
                            var cur = new Date(); cur.setHours(parseInt(p1[0]), parseInt(p1[1]), 0);
                            var end = new Date(); end.setHours(parseInt(p2[0]), parseInt(p2[1]), 0);
                            while (cur <= end) {
                                var h = cur.getHours().toString().padStart(2,'0');
                                var m = cur.getMinutes().toString().padStart(2,'0');
                                allOptions.push(h+':'+m);
                                cur.setMinutes(cur.getMinutes()+30);
                            }
                        }
                        addSlots(data.open_time, data.close_time);
                        addSlots(data.open_time_2, data.close_time_2);

                        if (allOptions.length === 0) {
                            messages.innerHTML = '<span style="color:red;">No hay horas disponibles para este día.</span>';
                            timeSelect.innerHTML = '<option value="">Sin horas</option>';
                            return;
                        }

                        timeSelect.innerHTML = '<option value="">Selecciona hora</option>';
                        for (var i = 0; i < allOptions.length; i++) {
                            var opt = document.createElement('option');
                            opt.value = allOptions[i];
                            opt.textContent = allOptions[i];
                            timeSelect.appendChild(opt);
                        }
                        timeSelect.disabled = false;
                        messages.innerHTML = '<span style="color:green;">Fecha disponible. Selecciona una hora.</span>';
                        btnSubmit.disabled = false;
                    })
                    .catch(function(err) {
                        messages.innerHTML = '<span style="color:red;">Error comprobando disponibilidad.</span>';
                        console.error('RRM Error:', err);
                    });
                });

                var form = document.getElementById('rrm-step-1');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        var date = dateInput.value;
                        var time = timeSelect.value;
                        var adults = parseInt(adultsInput.value) || 0;
                        var children = parseInt(childrenInput.value) || 0;
                        var name = document.getElementById('rrm-name').value;
                        var email = document.getElementById('rrm-email').value;
                        var phone = document.getElementById('rrm-phone').value;
                        var notes = document.getElementById('rrm-notes') ? document.getElementById('rrm-notes').value : '';

                        if (!date || !time || adults < 1 || !name || !email || !phone) {
                            messages.innerHTML = '<span style="color:red;">Por favor, completa todos los campos requeridos (mínimo 1 adulto).</span>';
                            messages.style.display = 'block';
                            return;
                        }

                        btnSubmit.disabled = true;
                        btnSubmit.innerText = 'Procesando...';
                        messages.innerHTML = '';
                        messages.style.display = 'none';

                        fetch(API_BASE + '/reservations', {
                            method: 'POST',
                            headers: {'Content-Type':'application/json','Accept':'application/json'},
                            body: JSON.stringify({
                                date: date+' '+time+':00',
                                people: adults+children,
                                adults: adults,
                                children: children,
                                name: name,
                                email: email,
                                phone: phone,
                                notes: notes,
                                allergies: document.getElementById('rrm-allergies') ? document.getElementById('rrm-allergies').checked : false,
                                celiac: document.getElementById('rrm-celiac') ? document.getElementById('rrm-celiac').checked : false,
                                strollers: document.getElementById('rrm-strollers') ? document.getElementById('rrm-strollers').checked : false,
                                reduced_mobility: document.getElementById('rrm-reduced-mobility') ? document.getElementById('rrm-reduced-mobility').checked : false,
                                wheelchairs: document.getElementById('rrm-wheelchairs') ? document.getElementById('rrm-wheelchairs').checked : false
                            })
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            btnSubmit.disabled = false;
                            btnSubmit.innerText = 'Confirmar Reserva';
                            messages.style.display = 'block';

                            if (data.reservation_id) {
                                var msg = '<span style="color:green;">¡Reserva confirmada con éxito! Revisa tu email.</span>';
                                if (data.discount_applied) {
                                    msg += '<br><span style="color:green;"><strong>¡PERTENECES AL CLUB SAGARETXE! Recuerda indicarlo para que te apliquemos tu descuento.</strong></span>';
                                }
                                messages.innerHTML = msg;
                                form.style.display = 'none';
                            } else if (data.message) {
                                messages.innerHTML = '<span style="color:red;">Error: ' + data.message + '</span>';
                            } else {
                                messages.innerHTML = '<span style="color:red;">Ha ocurrido un error inesperado.</span>';
                            }
                        })
                        .catch(function(err) {
                            btnSubmit.disabled = false;
                            btnSubmit.innerText = 'Confirmar Reserva';
                            messages.style.display = 'block';
                            messages.innerHTML = '<span style="color:red;">Error de conexión con el servidor.</span>';
                            console.error('RRM Error:', err);
                        });
                    });
                }
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }
}
