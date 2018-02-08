<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use Pronamic\WordPress\Pay\Core\GatewayConfigFactory;

/**
 * Title: Nocks config factory
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class ConfigFactory extends GatewayConfigFactory {
	public function get_config( $post_id ) {
		$config = new Config();

		$config->post_id          = $post_id;
		$config->mode             = get_post_meta( $post_id, '_pronamic_gateway_mode', true );
		$config->api_key          = get_post_meta( $post_id, '_pronamic_gateway_nocks_api_key', true );
		$config->merchant_profile = get_post_meta( $post_id, '_pronamic_gateway_nocks_merchant_profile', true );

		return $config;
	}
}
