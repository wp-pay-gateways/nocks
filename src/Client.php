<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use WP_Error;

/**
 * Title: Nocks client
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.1
 * @since   1.0.0
 */
class Client {
	/**
	 * URL Nocks API.
	 *
	 * @link https://docs.nocks.com/
	 *
	 * @var string
	 */
	const API_URL = 'https://api.nocks.com/api/v2/';

	/**
	 * URL Nocks sandbox API.
	 *
	 * @var string
	 */
	const NOCKS_DOMAIN = 'https://www.nocks.com/';

	/**
	 * Access Token.
	 *
	 * @var string
	 */
	private $access_token;

	/**
	 * Merchant profile.
	 *
	 * @var string
	 */
	private $merchant_profile;

	/**
	 * Error
	 *
	 * @var WP_Error
	 */
	private $error;

	/**
	 * Error
	 *
	 * @return WP_Error
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Get access token.
	 */
	public function get_access_token() {
		return $this->access_token;
	}

	/**
	 * Set access token.
	 *
	 * @param string $access_token Access token.
	 */
	public function set_access_token( $access_token ) {
		$this->access_token = $access_token;
	}

	/**
	 * Get merchant profile.
	 */
	public function get_merchant_profile() {
		return $this->merchant_profile;
	}

	/**
	 * Set merchant profile.
	 *
	 * @param string $merchant_profile Merchant profile id.
	 */
	public function set_merchant_profile( $merchant_profile ) {
		$this->merchant_profile = $merchant_profile;
	}

	/**
	 * Get issuers.
	 */
	public function get_issuers() {
		return array(
			'ABNANL2A' => __( 'ABN Amro', 'pronamic_ideal' ),
			'RABONL2U' => __( 'Rabobank', 'pronamic_ideal' ),
			'INGBNL2A' => __( 'ING Bank', 'pronamic_ideal' ),
			'SNSBNL2A' => __( 'SNS Bank', 'pronamic_ideal' ),
			'ASNBNL21' => __( 'ASN Bank', 'pronamic_ideal' ),
			'RBRBNL21' => __( 'RegioBank', 'pronamic_ideal' ),
			'TRIONL2U' => __( 'Triodos Bank', 'pronamic_ideal' ),
			'FVLBNL22' => __( 'Van Lanschot', 'pronamic_ideal' ),
			'KNABNL2H' => __( 'Knab', 'pronamic_ideal' ),
			'BUNQNL2A' => __( 'Bunq', 'pronamic_ideal' ),
			'MOYONL21' => __( 'Moneyou', 'pronamic_ideal' ),
		);
	}

	/**
	 * Send request with the specified action and parameters
	 *
	 * @param string $end_point              Request end point.
	 * @param string $method                 HTTP method.
	 * @param array  $data                   Data.
	 * @param int    $expected_response_code Expected response code.
	 *
	 * @return bool|object
	 */
	private function send_request( $end_point, $method = 'GET', array $data = array(), $expected_response_code = 200 ) {
		// Request.
		$url = self::API_URL . $end_point;

		if ( is_array( $data ) && ! empty( $data ) ) {
			$data = wp_json_encode( $data );
		}

		$response = wp_remote_request(
			$url,
			array(
				'method'  => $method,
				'headers' => array(
					'Accept'        => 'application/json',
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $this->get_access_token(),
				),
				'body'    => $data,
			)
		);

		// Response code.
		$response_code = wp_remote_retrieve_response_code( $response );

		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $expected_response_code != $response_code ) {
			$this->error = new WP_Error( 'nocks_error', 'Unexpected response code.' );
		}

		// Body.
		$body = wp_remote_retrieve_body( $response );

		$data = json_decode( $body );

		if ( ! is_object( $data ) ) {
			$this->error = new WP_Error( 'nocks_error', 'Could not parse response.' );

			return false;
		}

		// Nocks error.
		if ( isset( $data->error, $data->error->message ) ) {
			$this->error = new WP_Error( 'nocks_error', $data->error->message, $data->error );

			return false;
		}

		return $data;
	}

	/**
	 * Get merchant profiles.
	 *
	 * @return array
	 */
	public function get_merchant_profiles() {
		$profiles = array();

		$merchants = $this->send_request( 'merchant', 'GET' );

		if ( $merchants ) {
			foreach ( $merchants->data as $merchant ) {
				foreach ( $merchant->merchant_profiles->data as $profile ) {
					$profiles[ $profile->uuid ] = $merchant->name . ' - ' . $profile->name;
				}
			}
		}

		return $profiles;
	}

	/**
	 * Start transaction.
	 *
	 * @param Transaction $transaction Transaction object.
	 *
	 * @return array|bool|mixed|object
	 */
	public function start_transaction( Transaction $transaction ) {
		return $this->send_request(
			'transaction',
			'POST',
			$transaction->get_data(),
			201
		);
	}

	/**
	 * Get transaction.
	 *
	 * @param string $transaction_uuid Transaction UUID.
	 *
	 * @return array|bool|mixed|object
	 */
	public function get_transaction( $transaction_uuid ) {
		return $this->send_request(
			'transaction/' . $transaction_uuid,
			'GET'
		);
	}

	/**
	 * Get transaction quote.
	 *
	 * @param string $source_currency Source currency.
	 * @param string $target_currency Target currency.
	 * @param string $amount          Amount in given source currency.
	 * @param string $payment_method  Payment method.
	 *
	 * @return array|bool|mixed|object
	 */
	public function get_transaction_quote( $source_currency, $target_currency, $amount, $payment_method ) {
		$data = array(
			'source_currency'  => $source_currency,
			'target_currency'  => $target_currency,
			'merchant_profile' => $this->get_merchant_profile(),
			'amount'           => array(
				'amount'   => (string) $amount,
				'currency' => $source_currency,
			),
			'payment_method'   => array( 'method' => $payment_method ),
		);

		return $this->send_request(
			'transaction/quote',
			'POST',
			$data
		);
	}
}
