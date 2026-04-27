<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

// Fetch tables
$tables_table = $wpdb->prefix . 'rrm_tables';
$tables = $wpdb->get_results( "SELECT * FROM $tables_table" );

// Fetch today's unassigned reservations
$res_table = $wpdb->prefix . 'rrm_reservations';
$today = date('Y-m-d', current_time('timestamp'));
$unassigned_res = $wpdb->get_results( $wpdb->prepare( "
    SELECT * FROM $res_table 
    WHERE fecha = %s 
    AND (estado = 'confirmed' OR estado = 'pending')
    AND (table_id IS NULL OR table_id = 0)
", $today ) );

?>
<div class="wrap rrm-floor-plan-wrap">
    <h1>Plano de la Sala (Drag & Drop)</h1>
    <div class="rrm-floor-plan-container">
        
        <!-- Sidebar: Reservas del día sin asignar -->
        <div class="rrm-sidebar">
            <h2>Reservas de Hoy (Sin asignar)</h2>
            <div id="rrm-reservations-list">
                <?php if ( empty($unassigned_res) ) : ?>
                    <p>No hay reservas pendientes de asignar hoy.</p>
                <?php else : ?>
                    <?php foreach ( $unassigned_res as $res ) : ?>
                        <div class="rrm-draggable-res" data-res-id="<?php echo esc_attr( $res->id ); ?>" data-guests="<?php echo esc_attr( $res->comensales ); ?>">
                            <strong><?php echo esc_html( $res->nombre ); ?></strong><br>
                            <?php echo esc_html( $res->hora_inicio ); ?> | Comensales: <?php echo esc_html( $res->comensales ); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="rrm-controls" style="margin-top:20px;">
                <button type="button" id="btn-add-table" class="button">Añadir Mesa</button>
                <button type="button" id="btn-save-layout" class="button button-primary">Guardar Disposición</button>
            </div>
        </div>

        <!-- Canvas: Plano -->
        <div class="rrm-canvas-area" id="rrm-floor-canvas">
            <?php foreach ( $tables as $tbl ) : ?>
                <?php $forma_class = ($tbl->forma === 'round') ? 'round' : ''; ?>
                <div class="rrm-table draggable resizable <?php echo esc_attr($forma_class); ?>" 
                     data-table-id="<?php echo esc_attr( $tbl->id ); ?>"
                     data-forma="<?php echo esc_attr( $tbl->forma ); ?>"
                     data-x="<?php echo esc_attr( $tbl->pos_x ); ?>"
                     data-y="<?php echo esc_attr( $tbl->pos_y ); ?>"
                     style="width: <?php echo esc_attr( $tbl->ancho ); ?>px; height: <?php echo esc_attr( $tbl->alto ); ?>px; transform: translate(<?php echo esc_attr( $tbl->pos_x ); ?>px, <?php echo esc_attr( $tbl->pos_y ); ?>px);">
                    <span class="table-name"><?php echo esc_html( $tbl->nombre ); ?></span>
                    <span class="table-cap">(Cap: <?php echo esc_html( $tbl->capacidad ); ?>)</span>
                    <div class="rrm-table-reservations"></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    </div>

    <!-- Modal HTML -->
    <div id="rrm-table-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
        <div style="background-color:#fff; margin:10% auto; padding:20px; border:1px solid #888; width:350px; border-radius:8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            <h2 id="rrm-modal-title">Añadir Nueva Mesa</h2>
            <input type="hidden" id="rrm-modal-table-id" value="">
            <div style="margin-bottom:15px;">
                <label for="rrm-modal-name" style="display:block; margin-bottom:5px; font-weight:bold;">Nombre de la mesa</label>
                <input type="text" id="rrm-modal-name" class="regular-text" style="width:100%;">
            </div>
            <div style="margin-bottom:15px;">
                <label for="rrm-modal-cap" style="display:block; margin-bottom:5px; font-weight:bold;">Capacidad</label>
                <input type="number" id="rrm-modal-cap" class="regular-text" style="width:100%;" min="1" value="2">
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Forma</label>
                <label style="margin-right:15px;">
                    <input type="radio" name="rrm-modal-forma" value="rect" checked> Cuadrada/Rectangular
                </label>
                <label>
                    <input type="radio" name="rrm-modal-forma" value="round"> Redonda
                </label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <button type="button" id="btn-delete-table" class="button" style="color:#b32d2e; border-color:#b32d2e; display:none;">Borrar</button>
                </div>
                <div>
                    <button type="button" id="btn-cancel-table" class="button" style="margin-right:10px;">Cancelar</button>
                    <button type="button" id="btn-confirm-table" class="button button-primary">Añadir Mesa</button>
                </div>
            </div>
        </div>
    </div>
</div>
