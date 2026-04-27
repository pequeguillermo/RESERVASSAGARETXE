<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RRM_Email_Manager {

    private static function get_headers() {
        $sender_name = get_option('rrm_email_sender_name', 'Reservas Sagaretxe');
        $sender_email = get_option('rrm_email_sender_email', get_option('admin_email'));
        return array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $sender_name . ' <' . $sender_email . '>'
        );
    }

    private static function prepare_message($body, $reservation, $confirm_url = '', $cancel_url = '') {
        $search = ['{nombre}', '{fecha}', '{hora}', '{comensales}', '{confirm_link}', '{cancel_link}'];
        $replace = [
            $reservation->nombre,
            $reservation->fecha,
            $reservation->hora_inicio,
            $reservation->comensales,
            $confirm_url,
            $cancel_url
        ];
        return str_replace($search, $replace, $body);
    }

    public static function send_confirmation_email( $reservation_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rrm_reservations';
        $reservation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $reservation_id ) );

        if ( ! $reservation ) {
            return false;
        }

        $token = $reservation->confirmation_token;
        if ( empty( $token ) ) {
            $token = bin2hex( random_bytes( 16 ) );
            $wpdb->update($table, [ 'confirmation_token' => $token ], [ 'id' => $reservation_id ]);
        }
        
        $cancel_token = $reservation->cancel_token;
        if ( empty( $cancel_token ) ) {
            $cancel_token = bin2hex( random_bytes( 16 ) );
            $wpdb->update($table, [ 'cancel_token' => $cancel_token ], [ 'id' => $reservation_id ]);
        }

        $confirm_url = site_url( '/wp-json/rrm/v1/confirm?token=' . $token );
        $cancel_url = site_url( '/wp-json/rrm/v1/cancel?token=' . $cancel_token );

        $subject = get_option('rrm_conf_subject', 'Confirma tu reserva en Reservas Sagaretxe');
        $body_template = get_option('rrm_conf_body', "Hola {nombre},\n\nHas solicitado una reserva para el día {fecha} a las {hora} para {comensales} personas.\n\nPara confirmar tu reserva, por favor haz click en el siguiente enlace:\n{confirm_link}\n\nSi necesitas cancelar, usa este enlace:\n{cancel_link}\n\nSi no confirmas tu reserva, será cancelada automáticamente.\n\nGracias,\nReservas Sagaretxe");

        $message = self::prepare_message($body_template, $reservation, $confirm_url, $cancel_url);

        return wp_mail( $reservation->email, $subject, $message, self::get_headers() );
    }

    public static function send_reminder_email( $reservation, $type ) {
        $cancel_url = site_url( '/wp-json/rrm/v1/cancel?token=' . $reservation->cancel_token );
        
        if ($type === '12h') {
            $subject = get_option('rrm_rem12_subject', 'Recordatorio de tu reserva en Reservas Sagaretxe (Faltan 12 horas)');
            $body_template = get_option('rrm_rem12_body', "Hola {nombre},\n\nTe recordamos que tienes una reserva en unas 12 horas ({fecha} a las {hora}) para {comensales} personas.\n\nSi no puedes asistir, por favor cancela tu reserva usando este enlace:\n{cancel_link}\n\n¡Te esperamos!\nReservas Sagaretxe");
        } else {
            // 6h
            $subject = get_option('rrm_rem6_subject', 'Recordatorio de tu reserva en Reservas Sagaretxe (Faltan 6 horas)');
            $body_template = get_option('rrm_rem6_body', "Hola {nombre},\n\nTe recordamos que tienes una reserva hoy en unas 6 horas ({hora}) para {comensales} personas.\n\nSi tienes algún imprevisto, cancela tu reserva aquí:\n{cancel_link}\n\n¡Te esperamos!\nReservas Sagaretxe");
        }

        $message = self::prepare_message($body_template, $reservation, '', $cancel_url);

        return wp_mail( $reservation->email, $subject, $message, self::get_headers() );
    }

    public static function send_review_email( $reservation ) {
        $subject = get_option('rrm_rev_subject', '¿Qué tal fue tu experiencia en Reservas Sagaretxe?');
        $body_template = get_option('rrm_rev_body', "Hola {nombre},\n\nEsperamos que hayas disfrutado de tu visita el {fecha}.\n\nNos encantaría saber tu opinión. Por favor, déjanos una reseña aquí:\n[TU_ENLACE_DE_RESEÑA]\n\n¡Gracias y hasta pronto!\nReservas Sagaretxe");

        $message = self::prepare_message($body_template, $reservation);

        return wp_mail( $reservation->email, $subject, $message, self::get_headers() );
    }
}
