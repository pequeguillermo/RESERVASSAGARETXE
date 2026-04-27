<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$schedules = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rrm_schedule", OBJECT_K );

$dias = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    0 => 'Domingo'
];

?>
<div class="wrap">
    <h1 class="wp-heading-inline">Horario General</h1>
    <p>Establece el horario de apertura y cierre para cada día. Si marcas "Cerrado", el motor no permitirá reservas para ese día.</p>
    <hr class="wp-header-end">

    <?php if ( isset($_GET['msg']) && $_GET['msg'] === 'saved' ) : ?>
        <div class="notice notice-success is-dismissible"><p>Horario general guardado.</p></div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="rrm_save_schedule">
        <?php wp_nonce_field( 'rrm_save_schedule_action', 'rrm_save_schedule_nonce' ); ?>

        <table class="wp-list-table widefat fixed striped" style="max-width: 600px;">
            <thead>
                <tr>
                    <th>Día</th>
                    <th>Apertura</th>
                    <th>Cierre</th>
                    <th>Cerrado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $dias as $num => $nombre ) : 
                    $row = isset($schedules[$num]) ? $schedules[$num] : null;
                    $apertura = $row ? $row->hora_apertura : '12:00:00';
                    $cierre = $row ? $row->hora_cierre : '23:30:00';
                    $is_closed = $row ? $row->is_closed : 0;
                ?>
                    <tr>
                        <td><strong><?php echo $nombre; ?></strong></td>
                        <td><input type="time" name="schedule[<?php echo $num; ?>][apertura]" value="<?php echo esc_attr(substr($apertura,0,5)); ?>"></td>
                        <td><input type="time" name="schedule[<?php echo $num; ?>][cierre]" value="<?php echo esc_attr(substr($cierre,0,5)); ?>"></td>
                        <td><input type="checkbox" name="schedule[<?php echo $num; ?>][closed]" value="1" <?php checked($is_closed, 1); ?>></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php submit_button( 'Guardar Horario' ); ?>
    </form>
</div>
