<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use Pronamic\WordPress\Pay\Core\GatewaySettings;
use Pronamic\WordPress\Pay\Util as Pay_Util;

/**
 * Title: Nocks settings
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class Settings extends GatewaySettings {
	/**
	 * Settings constructor.
	 */
	public function __construct() {
		add_filter( 'pronamic_pay_gateway_sections', array( $this, 'sections' ) );
		add_filter( 'pronamic_pay_gateway_fields', array( $this, 'fields' ) );
	}

	/**
	 * Settings sections.
	 *
	 * @param array $sections Sections.
	 *
	 * @return array
	 */
	public function sections( array $sections ) {
		$sections['nocks'] = array(
			'title'   => __( 'Nocks', 'pronamic_ideal' ),
			'methods' => array( 'nocks' ),
		);

		// Transaction feedback.
		$sections['nocks_feedback'] = array(
			'title'       => __( 'Transaction feedback', 'pronamic_ideal' ),
			'methods'     => array( 'nocks' ),
			'description' => __( 'Payment status updates will be processed without any additional configuration. The <em>Webhook URL</em> is being used to receive the status updates.', 'pronamic_ideal' ),
		);

		return $sections;
	}

	/**
	 * Settings fields.
	 *
	 * @param array $fields Settings fields.
	 *
	 * @return array
	 */
	public function fields( array $fields ) {
		// API Key
		$fields[] = array(
			'filter'   => FILTER_SANITIZE_STRING,
			'section'  => 'nocks',
			'meta_key' => '_pronamic_gateway_nocks_api_key',
			'title'    => _x( 'Access Token', 'nocks', 'pronamic_ideal' ),
			'type'     => 'textarea',
			'classes'  => array( 'code' ),
		);

		// Merchant profile.
		$fields[] = array(
			'filter'   => FILTER_SANITIZE_STRING,
			'section'  => 'nocks',
			'meta_key' => '_pronamic_gateway_nocks_merchant_profile',
			'title'    => _x( 'Merchant Profile', 'nocks', 'pronamic_ideal' ),
			'type'     => 'description',
			'callback' => array( $this, 'field_merchant_profile' ),
		);

		// Transaction feedback.
		$fields[] = array(
			'section' => 'nocks',
			'title'   => __( 'Transaction feedback', 'pronamic_ideal' ),
			'type'    => 'description',
			'html'    => sprintf(
				'<span class="dashicons dashicons-yes"></span> %s',
				__( 'Payment status updates will be processed without any additional configuration.', 'pronamic_ideal' )
			),
		);

		// Webhook URL.
		$fields[] = array(
			'section'  => 'nocks_feedback',
			'title'    => __( 'Webhook URL', 'pronamic_ideal' ),
			'type'     => 'text',
			'classes'  => array( 'large-text', 'code' ),
			'value'    => add_query_arg( 'nocks_webhook', '', home_url( '/' ) ),
			'readonly' => true,
			'methods'  => array( 'nocks' ),
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
		$api_key          = get_post_meta( get_the_ID(), '_pronamic_gateway_nocks_api_key', true );
		$merchant_profile = get_post_meta( get_the_ID(), '_pronamic_gateway_nocks_merchant_profile', true );

		if ( ! $api_key ) {
			esc_html_e( 'First enter an API Key and save the configuration, to be able to choose from your Nocks merchant profiles.', 'pronamic_ideal' );

			return;
		}

		$client = new Client();

		$client->set_api_key( $api_key );

		// Select merchant profile.
		printf( '<select name="%s">', esc_attr( $field['meta_key'] ) );

		$options = array( array( 'options' => $client->get_merchant_profiles() ) );

		echo Pay_Util::select_options_grouped( $options, $merchant_profile ); // WPCS: xss ok.

		echo '</select>';
	}
}
