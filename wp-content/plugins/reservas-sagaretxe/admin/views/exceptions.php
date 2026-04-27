<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$exceptions = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rrm_exceptions ORDER BY fecha ASC" );
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Excepciones y Festivos</h1>
    <p>Añade días en los que el restaurante estará cerrado o tendrá una capacidad máxima diferente a la habitual.</p>
    <hr class="wp-header-end">

    <?php if ( isset($_GET['msg']) && $_GET['msg'] === 'saved' ) : ?>
        <div class="notice notice-success is-dismissible"><p>Excepción guardada.</p></div>
    <?php endif; ?>
    <?php if ( isset($_GET['msg']) && $_GET['msg'] === 'deleted' ) : ?>
        <div class="notice notice-warning is-dismissible"><p>Excepción eliminada.</p></div>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Aforo Especial</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $exceptions ) : ?>
                <?php foreach ( $exceptions as $exc ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( date('d/m/Y', strtotime($exc->fecha)) ); ?></strong></td>
                        <td>
                            <?php 
                                if ($exc->tipo === 'cerrado') echo '<span class="status-cancelled">Cerrado</span>';
                                else echo '<span class="status-confirmed">Horario/Capacidad Especial</span>';
                            ?>
                        </td>
                        <td><?php echo $exc->capacidad_especial ? intval($exc->capacidad_especial) . ' pax' : '-'; ?></td>
                        <td>
                            <a href="<?php echo admin_url( 'admin-post.php?action=rrm_delete_exception&id=' . $exc->id ); ?>" class="button button-link-delete" onclick="return confirm('¿Seguro que quieres borrar esta excepción?');">Borrar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="4">No hay excepciones configuradas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br><br>
    <h2>Añadir Excepción</h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="rrm_save_exception">
        <?php wp_nonce_field( 'rrm_save_exception_action', 'rrm_save_exception_nonce' ); ?>

        <table class="form-table">
            <tr>
                <th scope="row"><label for="fecha">Fecha</label></th>
                <td><input name="fecha" type="date" id="fecha" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="tipo">Tipo de Excepción</label></th>
                <td>
                    <select name="tipo" id="tipo">
                        <option value="cerrado">Cerrado todo el día</option>
                        <option value="capacidad_especial">Capacidad Especial</option>
                    </select>
                </td>
            </tr>
            <tr id="row-capacidad" style="display:none;">
                <th scope="row"><label for="capacidad_especial">Nuevo Aforo Máximo</label></th>
                <td><input name="capacidad_especial" type="number" id="capacidad_especial" min="1" class="small-text"> personas</td>
            </tr>
        </table>
        
        <?php submit_button( 'Guardar Excepción' ); ?>
    </form>

    <script>
        document.getElementById('tipo').addEventListener('change', function() {
            if (this.value === 'capacidad_especial') {
                document.getElementById('row-capacidad').style.display = 'table-row';
            } else {
                document.getElementById('row-capacidad').style.display = 'none';
            }
        });
    </script>
</div>
