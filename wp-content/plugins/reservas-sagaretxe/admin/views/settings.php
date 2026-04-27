<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$sender_name = get_option('rrm_email_sender_name', 'Reservas Sagaretxe');
$max_party = get_option('rrm_max_party_size', 20);
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Ajustes Generales</h1>
    <hr class="wp-header-end">

    <?php if ( isset($_GET['msg']) && $_GET['msg'] === 'saved' ) : ?>
        <div class="notice notice-success is-dismissible"><p>Ajustes guardados.</p></div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="rrm_save_settings">
        <?php wp_nonce_field( 'rrm_save_settings_action', 'rrm_save_settings_nonce' ); ?>

        <table class="form-table">
            <tr>
                <th scope="row"><label for="rrm_email_sender_name">Nombre remitente de Email</label></th>
                <td>
                    <input name="rrm_email_sender_name" type="text" id="rrm_email_sender_name" value="<?php echo esc_attr($sender_name); ?>" class="regular-text">
                    <p class="description">El nombre que aparecerá cuando los clientes reciban sus correos de confirmación o recordatorio.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="rrm_max_party_size">Máximo Comensales por Reserva</label></th>
                <td>
                    <input name="rrm_max_party_size" type="number" id="rrm_max_party_size" value="<?php echo esc_attr($max_party); ?>" class="small-text" min="1">
                    <p class="description">Límite de personas que se puede reservar de una vez en el formulario público.</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button( 'Guardar Ajustes' ); ?>
    </form>
</div>
