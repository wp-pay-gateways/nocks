<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Nocks payment methods
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class Methods {
	/**
	 * Constant for the Bancontact method.
	 *
	 * @var string
	 */
	const BANCONTACT = 'bancontact';

	/**
	 * Constant for the Giropay method.
	 *
	 * @var string
	 */
	const GIROPAY = 'giropay';

	/**
	 * Constant for the Gulden payment method.
	 *
	 * @var string
	 */
	const GULDEN = 'gulden';

	/**
	 * Constant for the iDEAL payment method.
	 *
	 * @var string
	 */
	const IDEAL = 'ideal';

	/**
	 * Constant for the SEPA payment method.
	 *
	 * @var string
	 */
	const SEPA = 'sepa';

	/////////////////////////////////////////////////

	/**
	 * Transform WordPress payment method to Nocks method.
	 *
	 * @since 1.0.0
	 *
	 * @param $payment_method
	 *
	 * @return string
	 */
	public static function transform( $payment_method ) {
		switch ( $payment_method ) {
			case PaymentMethods::BANCONTACT:
				return self::BANCONTACT;

			case PaymentMethods::GIROPAY:
				return self::GIROPAY;

			case PaymentMethods::GULDEN:
				return self::GULDEN;

			case PaymentMethods::IDEAL:
				return self::IDEAL;

			default:
				return null;
		}
	}
}
