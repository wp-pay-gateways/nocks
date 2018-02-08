<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use WP_Error;

/**
 * Title: Nocks client
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class Client {
	/**
	 * URL Nocks API.
	 *
	 * @see https://docs.nocks.com/
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

	//////////////////////////////////////////////////

	/**
	 * Error
	 *
	 * @var WP_Error
	 */
	private $error;

	//////////////////////////////////////////////////

	/**
	 * The URL.
	 *
	 * @var string
	 */
	private $url;

	//////////////////////////////////////////////////

	/**
	 * Error
	 *
	 * @return WP_Error
	 */
	public function get_error() {
		return $this->error;
	}

	//////////////////////////////////////////////////

	/**
	 * Get API key.
	 */
	public function get_api_key() {
		return $this->api_key;
	}

	/**
	 * Set API key.
	 */
	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;
	}

	//////////////////////////////////////////////////

	/**
	 * Get merchant profile.
	 */
	public function get_merchant_profile() {
		return $this->merchant_profile;
	}

	/**
	 * Set merchant profile.
	 */
	public function set_merchant_profile( $merchant_profile ) {
		$this->merchant_profile = $merchant_profile;
	}

	//////////////////////////////////////////////////

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
		);
	}

	//////////////////////////////////////////////////

	/**
	 * Send request with the specified action and parameters
	 *
	 * @param string $end_point
	 * @param string $method
	 * @param array $data
	 * @param int $expected_response_code
	 *
	 * @return bool|object
	 */
	private function send_request( $end_point, $method = 'GET', array $data = array(), $expected_response_code = 200 ) {
		// Request
		$url = self::API_URL . $end_point;

		if ( is_array( $data ) && ! empty( $data ) ) {
			$data = wp_json_encode( $data );
		}

		$response = wp_remote_request( $url, array(
			'method'  => $method,
			'headers' => array(
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
			),
			'body'    => $data,
		) );

		// Response code
		$response_code = wp_remote_retrieve_response_code( $response );

		if ( $expected_response_code != $response_code ) { // WPCS: loose comparison ok.
			$this->error = new WP_Error( 'nocks_error', 'Unexpected response code.' );
		}

		// Body
		$body = wp_remote_retrieve_body( $response );

		$data = json_decode( $body );

		if ( ! is_object( $data ) ) {
			$this->error = new WP_Error( 'nocks_error', 'Could not parse response.' );

			return false;
		}

		// Nocks error
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
	 * @param Transaction $transaction
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
	 * @param string $transaction_uuid
	 *
	 * @return array|bool|mixed|object
	 */
	public function get_transaction( $transaction_uuid ) {
		return $this->send_request(
			'transaction/' . $transaction_uuid,
			'GET'
		);
	}
}
