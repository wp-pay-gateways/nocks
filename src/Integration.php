<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use Pronamic\WordPress\Pay\Gateways\Common\AbstractIntegration;

/**
 * Title: Nocks integration
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   1.0.0
 */
class Integration extends AbstractIntegration {
	public function __construct() {
		$this->id            = 'nocks';
		$this->name          = 'Nocks - Checkout';
		$this->product_url   = 'https://www.nocks.com/';
		$this->dashboard_url = 'https://www.nocks.com/';
		$this->provider      = 'nocks';

		// Actions
		$function = array( __NAMESPACE__ . '\Listener', 'listen' );

		if ( ! has_action( 'wp_loaded', $function ) ) {
			add_action( 'wp_loaded', $function );
		}
	}

	public function get_config_factory_class() {
		return __NAMESPACE__ . '\ConfigFactory';
	}

	public function get_settings_class() {
		return __NAMESPACE__ . '\Settings';
	}

	/**
	 * Get required settings for this integration.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = parent::get_settings();

		$settings[] = 'nocks';

		return $settings;
	}
}
