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

		$transaction_uuid = file_get_contents( 'php://input' );
		$transaction_uuid = filter_input( INPUT_GET, 'uuid' );

		if ( empty( $transaction_uuid ) ) {
			return;
		}

		$query = new WP_Query( array(
			'post_type'      => GatewayPostType::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'meta_query'     => array(
				array(
					'key'   => '_pronamic_gateway_id',
					'value' => 'nocks',
				),
			),
		) );

		foreach ( $query->posts as $post ) {
			$factory = new ConfigFactory();

			$config = $factory->get_config( $post->ID );

			if ( '' === $config->api_key ) {
				continue;
			}

			// Client
			$client = new Client();

			$client->set_api_key( $config->api_key );
			$client->set_merchant_profile( $config->merchant_profile );

			$transaction = $client->get_transaction( $transaction_uuid );

			if ( ! $transaction ) {
				return;
			}

			$payment = get_pronamic_payment( $transaction->data->metadata->pronamic_payment_id );

			$payment->set_transaction_id( $transaction->data->uuid );
			$payment->set_meta( 'nocks_update_status', $transaction->data->status );

			Plugin::update_payment( $payment );
		}
	}
}
