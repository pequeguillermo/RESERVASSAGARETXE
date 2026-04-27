<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table = $wpdb->prefix . 'rrm_reservations';
$reservations = $wpdb->get_results( "SELECT * FROM $table ORDER BY fecha DESC, hora_inicio DESC LIMIT 100" );

?>
<div class="wrap">
    <h1>Listado de Reservas</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Email / Teléfono</th>
                <th>Fecha / Hora</th>
                <th>Comensales</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty($reservations) ) : ?>
                <tr><td colspan="7">No hay reservas.</td></tr>
            <?php else : ?>
                <?php foreach ( $reservations as $res ) : ?>
                    <tr>
                        <td>#<?php echo esc_html( $res->id ); ?></td>
                        <td><?php echo esc_html( $res->nombre ); ?></td>
                        <td><?php echo esc_html( $res->email ); ?><br><?php echo esc_html( $res->telefono ); ?></td>
                        <td><?php echo esc_html( $res->fecha ); ?><br><?php echo esc_html( $res->hora_inicio ); ?></td>
                        <td><?php echo esc_html( $res->comensales ); ?></td>
                        <td>
                            <?php 
                            $colors = [
                                'pending' => 'orange',
                                'confirmed' => 'green',
                                'confirmada_cliente' => '#10b981', // green shade
                                'cancelled' => 'red',
                                'cancelada_cliente' => '#ef4444', // red shade
                                'completed' => 'blue',
                                'no_show' => 'black'
                            ];
                            $color = $colors[$res->estado] ?? 'grey';
                            $estado_label = str_replace('_', ' ', strtoupper($res->estado));
                            ?>
                            <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                <?php echo esc_html( $estado_label ); ?>
                            </span>
                        </td>
                        <td>
                            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline;">
                                <input type="hidden" name="action" value="rrm_change_status">
                                <input type="hidden" name="reservation_id" value="<?php echo esc_attr( $res->id ); ?>">
                                <input type="hidden" name="new_status" value="confirmed">
                                <button type="submit" class="button button-primary" <?php disabled($res->estado, 'confirmed'); ?>>Confirmar</button>
                            </form>
                            
                            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline;">
                                <input type="hidden" name="action" value="rrm_change_status">
                                <input type="hidden" name="reservation_id" value="<?php echo esc_attr( $res->id ); ?>">
                                <input type="hidden" name="new_status" value="cancelled">
                                <button type="submit" class="button" <?php disabled($res->estado, 'cancelled'); ?>>Cancelar</button>
                            </form>

                            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline;">
                                <input type="hidden" name="action" value="rrm_change_status">
                                <input type="hidden" name="reservation_id" value="<?php echo esc_attr( $res->id ); ?>">
                                <input type="hidden" name="new_status" value="no_show">
                                <button type="submit" class="button button-secondary" style="color:red; border-color:red;" <?php disabled($res->estado, 'no_show'); ?>>No-Show</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
