<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use Pronamic\WordPress\Pay\Core\GatewayConfigFactory;

/**
 * Title: Nocks config factory
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   1.0.0
 */
class ConfigFactory extends GatewayConfigFactory {
	public function get_config( $post_id ) {
		$config = new Config();

		$config->post_id          = $post_id;
		$config->mode             = get_post_meta( $post_id, '_pronamic_gateway_mode', true );
		$config->access_token     = get_post_meta( $post_id, '_pronamic_gateway_nocks_access_token', true );
		$config->merchant_profile = get_post_meta( $post_id, '_pronamic_gateway_nocks_merchant_profile', true );

		return $config;
	}
}
