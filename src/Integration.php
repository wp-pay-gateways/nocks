<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use Pronamic\WordPress\Pay\AbstractGatewayIntegration;
use Pronamic\WordPress\Pay\Util;

/**
 * Title: Nocks integration
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   1.0.0
 */
class Integration extends AbstractGatewayIntegration {
	/**
	 * Construct Nocks integration.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'id'            => 'nocks',
				'name'          => 'Nocks - Checkout',
				'product_url'   => 'https://www.nocks.com/',
				'dashboard_url' => 'https://www.nocks.com/',
				'provider'      => 'nocks',
				'supports'      => array(
					'payment_status_request',
					'webhook',
					'webhook_log',
					'webhook_no_config',
				),
				'deprecated'    => true,
			)
		);

		parent::__construct( $args );

		// Actions
		$function = array( __NAMESPACE__ . '\Listener', 'listen' );

		if ( ! has_action( 'wp_loaded', $function ) ) {
			add_action( 'wp_loaded', $function );
		}
	}

	/**
	 * Get settings fields.
	 *
	 * @return array
	 */
	public function get_settings_fields() {
		$fields = array();

		// Access token.
		$fields[] = array(
			'section'  => 'general',
			'filter'   => FILTER_SANITIZE_STRING,
			'meta_key' => '_pronamic_gateway_nocks_access_token',
			'title'    => _x( 'Access Token', 'nocks', 'pronamic_ideal' ),
			'type'     => 'textarea',
			'classes'  => array( 'code' ),
		);

		// Merchant profile.
		$fields[] = array(
			'section'  => 'general',
			'filter'   => FILTER_SANITIZE_STRING,
			'meta_key' => '_pronamic_gateway_nocks_merchant_profile',
			'title'    => _x( 'Merchant Profile', 'nocks', 'pronamic_ideal' ),
			'type'     => 'description',
			'callback' => array( $this, 'field_merchant_profile' ),
		);

		// Webhook URL.
		$fields[] = array(
			'section'  => 'feedback',
			'title'    => __( 'Webhook URL', 'pronamic_ideal' ),
			'type'     => 'text',
			'classes'  => array( 'large-text', 'code' ),
			'value'    => add_query_arg( 'nocks_webhook', '', home_url( '/' ) ),
			'readonly' => true,
			'tooltip'  => __( 'The Webhook URL as sent with each transaction to receive automatic payment status updates on.', 'pronamic_ideal' ),
		);

		return $fields;
	}

	/**
	 * Field merchant profile select.
	 *
	 * @param array $field Settings field.
	 */
	public function field_merchant_profile( $field ) {
		$access_token     = get_post_meta( get_the_ID(), '_pronamic_gateway_nocks_access_token', true );
		$merchant_profile = get_post_meta( get_the_ID(), '_pronamic_gateway_nocks_merchant_profile', true );

		if ( ! $access_token ) {
			esc_html_e( 'First enter an API Key and save the configuration, to be able to choose from your Nocks merchant profiles.', 'pronamic_ideal' );

			return;
		}

		$client = new Client();

		$client->set_access_token( $access_token );

		// Select merchant profile.
		printf( '<select name="%s">', esc_attr( $field['meta_key'] ) );

		$options = array(
			__( '— Select Merchant Profile —', 'pronamic_ideal' ),
		);

		try {
			$options = array_merge( $options, $client->get_merchant_profiles() );
		} catch ( \Exception $e ) {
			// What to do?
		}

		$options = array(
			array(
				'options' => $options,
			),
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Util::select_options_grouped( $options, $merchant_profile );

		echo '</select>';
	}

	public function get_config( $post_id ) {
		$config = new Config();

		$config->mode             = $this->get_meta( $post_id, '_pronamic_gateway_mode' );
		$config->access_token     = $this->get_meta( $post_id, '_pronamic_gateway_nocks_access_token' );
		$config->merchant_profile = $this->get_meta( $post_id, '_pronamic_gateway_nocks_merchant_profile' );

		return $config;
	}

	/**
	 * Get gateway.
	 *
	 * @param int $post_id Post ID.
	 * @return Gateway
	 */
	public function get_gateway( $post_id ) {
		return new Gateway( $this->get_config( $post_id ) );
	}
}
