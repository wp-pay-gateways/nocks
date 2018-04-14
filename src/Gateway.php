<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use Pronamic\WordPress\Pay\Core\Gateway as Core_Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Title: Nocks gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class Gateway extends Core_Gateway {
	/**
	 * Constructs and initializes an Nocks gateway.
	 *
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		parent::__construct( $config );

		$this->set_method( Gateway::METHOD_HTTP_REDIRECT );
		$this->set_has_feedback( true );
		$this->set_amount_minimum( 0.01 );

		// Client
		$this->client = new Client();

		$this->client->set_api_key( $config->api_key );
		$this->client->set_merchant_profile( $config->merchant_profile );
	}

	/////////////////////////////////////////////////

	/**
	 * Get supported payment methods.
	 *
	 * @see Core_Gateway::get_supported_payment_methods()
	 */
	public function get_supported_payment_methods() {
		return array(
			PaymentMethods::BANCONTACT,
			PaymentMethods::GIROPAY,
			PaymentMethods::GULDEN,
			PaymentMethods::IDEAL,
		);
	}

	/**
	 * Is payment method required?
	 *
	 * @return bool
	 */
	public function payment_method_is_required() {
		return true;
	}

	public function get_issuer_field() {
		$payment_method = $this->get_payment_method();

		if ( null === $payment_method || PaymentMethods::IDEAL === $payment_method ) {
			return array(
				'id'       => 'pronamic_ideal_issuer_id',
				'name'     => 'pronamic_ideal_issuer_id',
				'label'    => __( 'Choose your bank', 'pronamic_ideal' ),
				'required' => true,
				'type'     => 'select',
				'choices'  => array( array( 'options' => $this->client->get_issuers() ) ),
			);
		}
	}

	/////////////////////////////////////////////////

	/**
	 * Start.
	 *
	 * @see Core_Gateway::start()
	 *
	 * @param Payment $payment
	 */
	public function start( Payment $payment ) {
		$payment_method = $payment->get_method();

		if ( empty( $payment_method ) ) {
			$payment_method = PaymentMethods::IDEAL;
		}

		$transaction = new Transaction();

		$transaction->payment_id       = $payment->get_id();
		$transaction->merchant_profile = $this->config->merchant_profile;
		$transaction->description      = $payment->get_description();
		$transaction->currency         = $payment->get_currency();
		$transaction->amount           = $payment->get_amount();
		$transaction->locale           = $payment->get_locale();
		$transaction->payment_method   = Methods::transform( $payment->get_method() );
		$transaction->redirect_url     = $payment->get_return_url();
		$transaction->callback_url     = add_query_arg( 'nocks_webhook', '', 'http://www.reuel.nl/' );
		$transaction->description      = $payment->get_description();

		if ( Methods::IDEAL === $transaction->payment_method ) {
			$transaction->issuer = $payment->get_issuer();
		}

		$result = $this->client->start_transaction( $transaction );

		$error = $this->client->get_error();

		if ( is_wp_error( $error ) ) {
			$this->error = $error;

			return;
		}

		if ( isset( $result->data->payments->data[0]->metadata->url ) ) {
			$payment->set_action_url( $result->data->payments->data[0]->metadata->url );
		}
	}

	/////////////////////////////////////////////////

	/**
	 * Update status of the specified payment.
	 *
	 * @param Payment $payment
	 */
	public function update_status( Payment $payment ) {
		$input_status = null;

		// Update status on customer return
		if ( filter_has_var( INPUT_GET, 'transactionId' ) && filter_has_var( INPUT_GET, 'status' ) ) {
			$transaction_uuid = filter_input( INPUT_GET, 'transactionId', FILTER_SANITIZE_STRING );

			$transaction = $this->client->get_transaction( $transaction_uuid );

			if ( $transaction ) {
				$payment->set_transaction_id( $transaction->data->uuid );

				$input_status = $transaction->data->status;
			}
		}

		// Update status via webhook
		if ( isset( $payment->meta['nocks_update_status'] ) ) {
			$input_status = $payment->meta['nocks_update_status'];

			$payment->set_meta( 'nocks_update_status', null );
		}

		if ( ! $input_status ) {
			return;
		}

		// Update payment status
		$status = Statuses::transform( $input_status );

		$payment->set_status( $status );
	}
}
