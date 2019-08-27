<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use Pronamic\WordPress\Pay\Core\Gateway as Core_Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\Statuses as Core_Statuses;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Title: Nocks gateway
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.1
 * @since   1.0.0
 */
class Gateway extends Core_Gateway {
	/**
	 * Slug of this gateway
	 *
	 * @var string
	 */
	const SLUG = 'nocks';

	/**
	 * Client.
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * Constructs and initializes an Nocks gateway.
	 *
	 * @param Config $config Config.
	 */
	public function __construct( Config $config ) {
		parent::__construct( $config );

		$this->set_method( self::METHOD_HTTP_REDIRECT );

		// Supported features.
		$this->supports = array(
			'payment_status_request',
		);

		// Client.
		$this->client = new Client();

		$this->client->set_access_token( $config->access_token );
		$this->client->set_merchant_profile( $config->merchant_profile );
	}

	/**
	 * Get supported payment methods.
	 *
	 * @see Core_Gateway::get_supported_payment_methods()
	 */
	public function get_supported_payment_methods() {
		return array(
			PaymentMethods::GULDEN,
		);
	}

	/**
	 * Start.
	 *
	 * @see Core_Gateway::start()
	 *
	 * @param Payment $payment The payment.
	 */
	public function start( Payment $payment ) {
		$payment_method = $payment->get_method();
		$currency       = $payment->get_total_amount()->get_currency()->get_alphabetic_code();
		$amount         = $payment->get_total_amount()->get_value();

		if ( empty( $payment_method ) ) {
			$payment_method = PaymentMethods::GULDEN;
		}

		if ( PaymentMethods::GULDEN === $payment_method ) {
			switch ( $currency ) {
				case 'EUR':
					// Convert to EUR.
					$quote = $this->client->get_transaction_quote( 'EUR', 'NLG', $amount, Methods::IDEAL );

					if ( $quote ) {
						$amount   = $quote->data->target_amount->amount;
						$currency = 'NLG';
					}

					break;
			}
		}

		$transaction = new Transaction();

		$transaction->payment_id       = $payment->get_id();
		$transaction->merchant_profile = $this->config->merchant_profile;
		$transaction->description      = $payment->get_description();
		$transaction->currency         = $currency;
		$transaction->amount           = $amount;
		$transaction->payment_method   = Methods::transform( $payment->get_method() );
		$transaction->redirect_url     = $payment->get_return_url();
		$transaction->callback_url     = add_query_arg( 'nocks_webhook', '', home_url( '/' ) );
		$transaction->description      = $payment->get_description();

		if ( null !== $payment->get_customer() ) {
			$transaction->locale = $payment->get_customer()->get_locale();
		}

		// Issuer.
		if ( Methods::IDEAL === $transaction->payment_method ) {
			$transaction->issuer = $payment->get_issuer();
		}

		// Start transaction.
		$result = $this->client->start_transaction( $transaction );

		// Handle errors.
		$error = $this->client->get_error();

		if ( is_wp_error( $error ) ) {
			$this->error = $error;

			return;
		}

		// Update payment.
		if ( isset( $result->data->payments->data[0]->uuid ) ) {
			$payment->set_transaction_id( $result->data->uuid );
		}

		if ( isset( $result->data->payments->data[0]->metadata->url ) ) {
			$payment->set_action_url( $result->data->payments->data[0]->metadata->url );
		}
	}

	/**
	 * Update status of the specified payment.
	 *
	 * @param Payment $payment The payment.
	 */
	public function update_status( Payment $payment ) {
		$transaction_id = $payment->get_transaction_id();

		$nocks_payment = $this->client->get_transaction( $transaction_id );

		if ( ! $nocks_payment ) {
			$payment->set_status( Core_Statuses::FAILURE );

			$this->error = $this->client->get_error();

			return;
		}

		if ( is_object( $nocks_payment ) && isset( $nocks_payment->data->status ) ) {
			$status = Statuses::transform( $nocks_payment->data->status );

			$payment->set_status( $status );
		}
	}
}
