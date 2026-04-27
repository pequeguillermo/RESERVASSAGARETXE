<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$shifts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rrm_shifts ORDER BY hora_inicio ASC" );
$schedules_raw = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rrm_schedule" );

$schedules = [];
if ($schedules_raw) {
    foreach ($schedules_raw as $s) {
        $schedules[$s->dia_semana] = $s;
    }
}

// Nombres de los días
$nombres_dias = [
    1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
    4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo'
];

// Generador de desplegable de horas
function rrm_generate_time_options() {
    $html = '';
    for($h=0; $h<24; $h++) {
        foreach(['00', '30'] as $m) {
            $val = sprintf('%02d:%s:00', $h, $m);
            $label = sprintf('%02d:%s', $h, $m);
            $html .= "<option value=\"{$val}\">{$label}</option>";
        }
    }
    return $html;
}

function rrm_check_shift_warnings($shift, $shifts, $schedules, $nombres_dias) {
    $warnings = [];
    $activos = explode(',', $shift->dias_activos);
    
    foreach ($activos as $dia) {
        if (isset($schedules[$dia])) {
            $sch = $schedules[$dia];
            if ($sch->is_closed) {
                $warnings[] = "Día {$nombres_dias[$dia]} marcado como cerrado en Horario Base.";
            } else {
                if ($shift->hora_inicio < $sch->hora_apertura || $shift->hora_fin > $sch->hora_cierre) {
                    $warnings[] = "Fuera de horario base el {$nombres_dias[$dia]} ({$sch->hora_apertura} - {$sch->hora_cierre}).";
                }
            }
        }
    }

    foreach ($shifts as $other) {
        if ($other->id === $shift->id) continue;
        $other_activos = explode(',', $other->dias_activos);
        $intersect = array_intersect($activos, $other_activos);
        if (!empty($intersect)) {
            if ($shift->hora_inicio < $other->hora_fin && $shift->hora_fin > $other->hora_inicio) {
                $warnings[] = "Solapamiento con turno '{$other->nombre}'.";
                break; 
            }
        }
    }

    return $warnings;
}

?>
<div class="wrap">
    <h1 class="wp-heading-inline">Turnos</h1>
    <hr class="wp-header-end">

    <?php if ( isset($_GET['msg']) && $_GET['msg'] === 'saved' ) : ?>
        <div class="notice notice-success is-dismissible"><p>Turno guardado correctamente.</p></div>
    <?php endif; ?>
    <?php if ( isset($_GET['msg']) && $_GET['msg'] === 'deleted' ) : ?>
        <div class="notice notice-warning is-dismissible"><p>Turno eliminado correctamente.</p></div>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Horario</th>
                <th>Capacidad</th>
                <th>Días Activos</th>
                <th>Avisos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $shifts ) : ?>
                <?php foreach ( $shifts as $shift ) : 
                    $warnings = rrm_check_shift_warnings($shift, $shifts, $schedules, $nombres_dias);
                ?>
                    <tr>
                        <td><strong><?php echo esc_html( $shift->nombre ); ?></strong></td>
                        <td><?php echo esc_html( substr($shift->hora_inicio,0,5) . ' - ' . substr($shift->hora_fin,0,5) ); ?></td>
                        <td><?php echo intval( $shift->capacidad_maxima ); ?> pax</td>
                        <td>
                            <?php 
                                $activos = explode(',', $shift->dias_activos);
                                $nombres_dias_activos = array_map(function($d) use ($nombres_dias) { return isset($nombres_dias[$d]) ? $nombres_dias[$d] : $d; }, $activos);
                                echo esc_html( implode(', ', $nombres_dias_activos) );
                            ?>
                        </td>
                        <td>
                            <?php if (!empty($warnings)) : ?>
                                <span class="dashicons dashicons-warning" style="color: #f56e28;" title="<?php echo esc_attr(implode("\n", $warnings)); ?>"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-yes-alt" style="color: #46b450;" title="Todo correcto"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="button edit-shift-btn" data-shift='<?php echo esc_attr(json_encode($shift)); ?>'>Editar</button>
                            <a href="<?php echo admin_url( 'admin-post.php?action=rrm_delete_shift&id=' . $shift->id ); ?>" class="button button-link-delete" onclick="return confirm('¿Seguro que quieres borrar este turno?');">Borrar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="6">No hay turnos configurados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <h2 id="form-title">Añadir Turno</h2>
    <form method="post" id="shift-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="rrm_save_shift">
        <input type="hidden" name="shift_id" id="shift_id" value="">
        <?php wp_nonce_field( 'rrm_save_shift_action', 'rrm_save_shift_nonce' ); ?>

        <table class="form-table">
            <tr>
                <th scope="row"><label for="nombre">Nombre del Turno</label></th>
                <td><input name="nombre" type="text" id="nombre" required class="regular-text" placeholder="Ej: Comida"></td>
            </tr>
            <tr>
                <th scope="row"><label>Horario</label></th>
                <td>
                    <select name="hora_inicio" id="hora_inicio" required>
                        <option value="">-- Inicio --</option>
                        <?php echo rrm_generate_time_options(); ?>
                    </select>
                    a 
                    <select name="hora_fin" id="hora_fin" required>
                        <option value="">-- Fin --</option>
                        <?php echo rrm_generate_time_options(); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="capacidad_maxima">Capacidad Máxima</label></th>
                <td><input name="capacidad_maxima" type="number" id="capacidad_maxima" required min="1" class="small-text"> personas</td>
            </tr>
            <tr>
                <th scope="row">Días Activos</th>
                <td>
                    <fieldset id="dias-checkboxes">
                        <label><input type="checkbox" name="dias[]" value="1" checked> Lunes</label><br>
                        <label><input type="checkbox" name="dias[]" value="2" checked> Martes</label><br>
                        <label><input type="checkbox" name="dias[]" value="3" checked> Miércoles</label><br>
                        <label><input type="checkbox" name="dias[]" value="4" checked> Jueves</label><br>
                        <label><input type="checkbox" name="dias[]" value="5" checked> Viernes</label><br>
                        <label><input type="checkbox" name="dias[]" value="6" checked> Sábado</label><br>
                        <label><input type="checkbox" name="dias[]" value="0" checked> Domingo</label>
                    </fieldset>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Turno">
            <button type="button" class="button" id="cancel-edit" style="display:none;" onclick="cancelEdit()">Cancelar Edición</button>
        </p>
    </form>

    <script>
        var existingShifts = <?php echo json_encode($shifts ? $shifts : []); ?>;
        var schedules = <?php echo json_encode($schedules); ?>;
        var nombresDias = <?php echo json_encode($nombres_dias); ?>;

        // Lógica de edición
        document.querySelectorAll('.edit-shift-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var shift = JSON.parse(this.getAttribute('data-shift'));
                
                document.getElementById('shift_id').value = shift.id;
                document.getElementById('nombre').value = shift.nombre;
                document.getElementById('hora_inicio').value = shift.hora_inicio;
                document.getElementById('hora_fin').value = shift.hora_fin;
                document.getElementById('capacidad_maxima').value = shift.capacidad_maxima;
                
                var diasActivos = shift.dias_activos.split(',');
                document.querySelectorAll('#dias-checkboxes input[type="checkbox"]').forEach(function(chk) {
                    chk.checked = diasActivos.includes(chk.value);
                });

                document.getElementById('form-title').innerText = "Editar Turno: " + shift.nombre;
                document.getElementById('submit').value = "Actualizar Turno";
                document.getElementById('cancel-edit').style.display = "inline-block";
                
                window.scrollTo({ top: document.getElementById('shift-form').offsetTop, behavior: 'smooth' });
            });
        });

        function cancelEdit() {
            document.getElementById('shift_id').value = "";
            document.getElementById('shift-form').reset();
            document.getElementById('form-title').innerText = "Añadir Turno";
            document.getElementById('submit').value = "Guardar Turno";
            document.getElementById('cancel-edit').style.display = "none";
        }

        document.getElementById('shift-form').addEventListener('submit', function(e) {
            var shiftId = document.getElementById('shift_id').value;
            var inicio = document.getElementById('hora_inicio').value;
            var fin = document.getElementById('hora_fin').value;
            var diasChecks = document.querySelectorAll('input[name="dias[]"]:checked');
            var dias = Array.from(diasChecks).map(el => el.value);
            
            if (inicio >= fin) {
                alert("La hora de inicio debe ser anterior a la hora de fin.");
                e.preventDefault();
                return;
            }

            if (dias.length === 0) {
                alert("Debes seleccionar al menos un día activo.");
                e.preventDefault();
                return;
            }

            var warnings = [];
            
            // Comprobar horarios base
            dias.forEach(function(d) {
                if (schedules[d]) {
                    if (schedules[d].is_closed == 1) {
                        warnings.push("El día " + nombresDias[d] + " está cerrado en Horario Base.");
                    } else if (inicio < schedules[d].hora_apertura || fin > schedules[d].hora_cierre) {
                        warnings.push("El turno (" + inicio.substring(0,5) + " - " + fin.substring(0,5) + ") sale fuera del horario base del " + nombresDias[d] + " (" + schedules[d].hora_apertura.substring(0,5) + " - " + schedules[d].hora_cierre.substring(0,5) + ").");
                    }
                }
            });

            // Comprobar solapamientos
            var overlapFound = false;
            existingShifts.forEach(function(other) {
                if (shiftId && other.id == shiftId) return; // No compararse consigo mismo al editar
                
                var otherDias = other.dias_activos.split(',');
                var intersect = dias.filter(value => otherDias.includes(value));
                if (intersect.length > 0) {
                    if (inicio < other.hora_fin && fin > other.hora_inicio) {
                        if(!overlapFound) {
                            warnings.push("El turno se solapa en el horario con '" + other.nombre + "' (" + other.hora_inicio.substring(0,5) + " - " + other.hora_fin.substring(0,5) + ").");
                            overlapFound = true;
                        }
                    }
                }
            });

            if (warnings.length > 0) {
                var msg = "⚠️ ATENCIÓN: Se han detectado incidencias:\n\n- " + warnings.join("\n- ") + "\n\n¿Quieres permitirlo y guardar el turno de todos modos?";
                if (!confirm(msg)) {
                    e.preventDefault();
                }
            }
        });
    </script>
</div>
