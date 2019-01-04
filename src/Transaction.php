<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use Pronamic\WordPress\Pay\Core\Util as Core_Util;

/**
 * Title: Nocks transaction
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   1.0.0
 */
class Transaction {
	public $merchant_profile;

	public $currency;

	public $amount;

	public $payment_method;

	public $issuer;

	public $metadata;

	public $order_id;

	public $description;

	public $payment_id;

	public $redirect_url;

	public $callback_url;

	public $locale;

	public function get_data() {
		return array(
			'merchant_profile' => $this->merchant_profile,
			'source_currency'  => $this->currency,
			'amount'           => array(
				'currency' => $this->currency,
				'amount'   => (string) $this->amount,
			),
			'payment_method'   => array(
				'method'   => $this->payment_method,
				'metadata' => array(
					'issuer' => $this->issuer,
				),
			),
			'metadata'         => array(
				'pronamic_payment_id' => $this->payment_id,
			),
			'description'      => $this->description,
			'redirect_url'     => $this->redirect_url,
			'callback_url'     => $this->callback_url,
			'locale'           => $this->locale,
		);
	}
}
