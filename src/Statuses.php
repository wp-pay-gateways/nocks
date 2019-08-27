<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use Pronamic\WordPress\Pay\Core\Statuses as Core_Statuses;

/**
 * Title: Nocks statuses
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   1.0.0
 */
class Statuses {
	/**
	 * Cancelled
	 *
	 * @var string
	 */
	const CANCELLED = 'cancelled';

	/**
	 * Completed
	 *
	 * @var string
	 */
	const COMPLETED = 'completed';

	/**
	 * Expired.
	 *
	 * @var string
	 */
	const EXPIRED = 'expired';

	/**
	 * Failed
	 *
	 * @var string
	 */
	const FAILED = 'failed';

	/**
	 * Pending
	 *
	 * @var string
	 */
	const PENDING = 'pending';

	/**
	 * Processing
	 *
	 * @var string
	 */
	const PROCESSING = 'processing';

	/**
	 * Transform a Nocks status to Pronamic Pay status.
	 *
	 * @param string $status
	 *
	 * @return string|null
	 */
	public static function transform( $status ) {
		switch ( $status ) {
			case self::CANCELLED:
				return Core_Statuses::CANCELLED;

			case self::COMPLETED:
				return Core_Statuses::SUCCESS;

			case self::EXPIRED:
				return Core_Statuses::EXPIRED;

			case self::FAILED:
				return Core_Statuses::FAILURE;

			case self::PENDING:
				return Core_Statuses::OPEN;

			default:
				return null;
		}
	}
}
