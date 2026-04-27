<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Opciones generales
$sender_name = get_option('rrm_email_sender_name', 'Reservas Sagaretxe');
$sender_email = get_option('rrm_email_sender_email', get_option('admin_email'));

// Textos por defecto
$default_conf_subject = 'Confirma tu reserva en Reservas Sagaretxe';
$default_conf_body = "Hola {nombre},\n\nHas solicitado una reserva para el día {fecha} a las {hora} para {comensales} personas.\n\nPara confirmar tu reserva, por favor haz click en el siguiente enlace:\n{confirm_link}\n\nSi necesitas cancelar, usa este enlace:\n{cancel_link}\n\nSi no confirmas tu reserva, será cancelada automáticamente.\n\nGracias,\nReservas Sagaretxe";

$default_12h_subject = 'Recordatorio de tu reserva en Reservas Sagaretxe (Faltan 12 horas)';
$default_12h_body = "Hola {nombre},\n\nTe recordamos que tienes una reserva en unas 12 horas ({fecha} a las {hora}) para {comensales} personas.\n\nSi no puedes asistir, por favor cancela tu reserva usando este enlace:\n{cancel_link}\n\n¡Te esperamos!\nReservas Sagaretxe";

$default_6h_subject = 'Recordatorio de tu reserva en Reservas Sagaretxe (Faltan 6 horas)';
$default_6h_body = "Hola {nombre},\n\nTe recordamos que tienes una reserva hoy en unas 6 horas ({hora}) para {comensales} personas.\n\nSi tienes algún imprevisto, cancela tu reserva aquí:\n{cancel_link}\n\n¡Te esperamos!\nReservas Sagaretxe";

$default_review_subject = '¿Qué tal fue tu experiencia en Reservas Sagaretxe?';
$default_review_body = "Hola {nombre},\n\nEsperamos que hayas disfrutado de tu visita el {fecha}.\n\nNos encantaría saber tu opinión. Por favor, déjanos una reseña aquí:\n[TU_ENLACE_DE_RESEÑA]\n\n¡Gracias y hasta pronto!\nReservas Sagaretxe";

// Obtener opciones
$conf_subj = get_option('rrm_conf_subject', $default_conf_subject);
$conf_body = get_option('rrm_conf_body', $default_conf_body);

$rem12_subj = get_option('rrm_rem12_subject', $default_12h_subject);
$rem12_body = get_option('rrm_rem12_body', $default_12h_body);

$rem6_subj = get_option('rrm_rem6_subject', $default_6h_subject);
$rem6_body = get_option('rrm_rem6_body', $default_6h_body);

$rev_subj = get_option('rrm_rev_subject', $default_review_subject);
$rev_body = get_option('rrm_rev_body', $default_review_body);
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Configuración de Emails</h1>
    <hr class="wp-header-end" />
    <?php if ( isset($_GET['msg']) && $_GET['msg'] === 'saved' ) : ?>
        <div class="notice notice-success is-dismissible"><p>Ajustes de email guardados.</p></div>
    <?php endif; ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="rrm_save_email_settings" />
        <?php wp_nonce_field( 'rrm_save_email_settings_action', 'rrm_save_email_settings_nonce' ); ?>
        
        <h2>Ajustes Generales</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="rrm_email_sender_name">Nombre remitente</label></th>
                <td><input name="rrm_email_sender_name" type="text" id="rrm_email_sender_name" value="<?php echo esc_attr($sender_name); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="rrm_email_sender_email">Email remitente</label></th>
                <td><input name="rrm_email_sender_email" type="email" id="rrm_email_sender_email" value="<?php echo esc_attr($sender_email); ?>" class="regular-text" /></td>
            </tr>
        </table>

        <p class="description"><strong>Variables disponibles para el cuerpo de los correos:</strong> <code>{nombre}</code>, <code>{fecha}</code>, <code>{hora}</code>, <code>{comensales}</code>, <code>{confirm_link}</code>, <code>{cancel_link}</code>.</p>

        <h2>1. Confirmación de Reserva</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="rrm_conf_subject">Asunto</label></th>
                <td><input name="rrm_conf_subject" type="text" id="rrm_conf_subject" value="<?php echo esc_attr($conf_subj); ?>" class="large-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="rrm_conf_body">Mensaje</label></th>
                <td><textarea name="rrm_conf_body" id="rrm_conf_body" class="large-text" rows="8"><?php echo esc_textarea($conf_body); ?></textarea></td>
            </tr>
        </table>

        <h2>2. Recordatorio 12 horas antes</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="rrm_rem12_subject">Asunto</label></th>
                <td><input name="rrm_rem12_subject" type="text" id="rrm_rem12_subject" value="<?php echo esc_attr($rem12_subj); ?>" class="large-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="rrm_rem12_body">Mensaje</label></th>
                <td><textarea name="rrm_rem12_body" id="rrm_rem12_body" class="large-text" rows="8"><?php echo esc_textarea($rem12_body); ?></textarea></td>
            </tr>
        </table>

        <h2>3. Recordatorio 6 horas antes</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="rrm_rem6_subject">Asunto</label></th>
                <td><input name="rrm_rem6_subject" type="text" id="rrm_rem6_subject" value="<?php echo esc_attr($rem6_subj); ?>" class="large-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="rrm_rem6_body">Mensaje</label></th>
                <td><textarea name="rrm_rem6_body" id="rrm_rem6_body" class="large-text" rows="8"><?php echo esc_textarea($rem6_body); ?></textarea></td>
            </tr>
        </table>

        <h2>4. Petición de Reseña (24h después)</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="rrm_rev_subject">Asunto</label></th>
                <td><input name="rrm_rev_subject" type="text" id="rrm_rev_subject" value="<?php echo esc_attr($rev_subj); ?>" class="large-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="rrm_rev_body">Mensaje</label></th>
                <td><textarea name="rrm_rev_body" id="rrm_rev_body" class="large-text" rows="8"><?php echo esc_textarea($rev_body); ?></textarea>
                <p class="description">Recuerda poner el enlace a tu ficha de Google o TripAdvisor donde dice [TU_ENLACE_DE_RESEÑA].</p></td>
            </tr>
        </table>

        <?php submit_button( 'Guardar Ajustes de Email' ); ?>
    </form>
</div>
