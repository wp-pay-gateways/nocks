<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use Pronamic\WordPress\Pay\GatewayPostType;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Core\Gateway;
use WP_Query;

/**
 * Title: Nocks listener
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class Listener {
	public static function listen() {
		if ( ! filter_has_var( INPUT_GET, 'nocks_webhook' ) ) {
			return;
		}

		$transaction_id = file_get_contents( 'php://input' );

		$payment = get_pronamic_payment_by_transaction_id( $transaction_id );

		if ( null === $payment ) {
			return;
		}

		// Add note.
		$note = sprintf(
			/* translators: %s: Nocks */
			__( 'Webhook requested by %s.', 'pronamic_ideal' ),
			__( 'Nocks', 'pronamic_ideal' )
		);

		$payment->add_note( $note );

		// Update payment.
		Plugin::update_payment( $payment, false );
	}
}
