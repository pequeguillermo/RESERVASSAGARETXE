<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table = $wpdb->prefix . 'rrm_graylist';
$graylist = $wpdb->get_results( "SELECT * FROM $table ORDER BY contador_no_show DESC" );

?>
<div class="wrap">
    <h1>Lista Gris (Control de No-Shows)</h1>
    <p>Los correos en esta lista con 2 o más no-shows son bloqueados automáticamente del motor de reservas frontend.</p>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Contador de No-Shows</th>
                <th>Última Incidencia</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty($graylist) ) : ?>
                <tr><td colspan="5">No hay correos en la lista gris.</td></tr>
            <?php else : ?>
                <?php foreach ( $graylist as $gl ) : ?>
                    <tr>
                        <td>#<?php echo esc_html( $gl->id ); ?></td>
                        <td>
                            <?php echo esc_html( $gl->email ); ?>
                            <?php if ($gl->contador_no_show >= 2) echo '<span style="color:red; font-weight:bold;"> (BLOQUEADO)</span>'; ?>
                        </td>
                        <td><?php echo esc_html( $gl->contador_no_show ); ?></td>
                        <td><?php echo esc_html( $gl->ultima_incidencia ); ?></td>
                        <td>
                            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline;">
                                <input type="hidden" name="action" value="rrm_remove_graylist">
                                <input type="hidden" name="graylist_id" value="<?php echo esc_attr( $gl->id ); ?>">
                                <button type="submit" class="button">Perdonar (Eliminar)</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
